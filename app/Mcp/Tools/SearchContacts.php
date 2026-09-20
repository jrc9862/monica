<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithMonica;
use App\Models\Contact;
use App\Models\Vault;
use Illuminate\Support\Collection;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

use function Safe\preg_split;

#[IsReadOnly]
class SearchContacts extends Tool
{
    use InteractsWithMonica;

    /**
     * The name columns a query is matched against.
     */
    private const NAME_COLUMNS = ['first_name', 'last_name', 'middle_name', 'nickname', 'maiden_name'];

    /**
     * Rows to score per chunk during the typo-tolerant pass.
     */
    private const TYPO_CHUNK = 500;

    public function description(): string
    {
        return 'Search for contacts by name (first, last, middle, nickname, maiden name) within a vault. '
            .'Use this to find a contact before reading or editing their details. Matching is fuzzy: '
            .'partial names work ("Jam" finds "James"), so do misspellings ("Levelace" finds "Lovelace"), '
            .'and multi-word queries match a contact that hits any of the words, ranked best-first. '
            .'An empty result means nothing in the vault resembles the term.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('query')
            ->description('The name to search for. A partial name or a misspelling is fine.')
            ->required()
            ->string('vault_id')
            ->description('The vault to search in. Required if the user has access to more than one vault.')
            ->optional()
            ->integer('limit')
            ->description('Maximum number of results to return (default 10, max 25).')
            ->optional();
    }

    public function handle(array $arguments): ToolResult
    {
        return $this->guard(function () use ($arguments) {
            $this->ensureTokenCan('read');

            $author = $this->author();
            $vaultId = $this->resolveVaultId($author, $arguments);

            if (! $author->vaults()->where('vaults.id', $vaultId)->exists()) {
                return ToolResult::error('The user does not have access to this vault.');
            }

            $vault = Vault::where('account_id', $author->account_id)->findOrFail($vaultId);

            $limit = max(1, min((int) ($arguments['limit'] ?? 10), 25));
            $terms = $this->terms((string) $this->required($arguments, 'query'));

            if ($terms === []) {
                return ToolResult::json(['contacts' => []]);
            }

            $contacts = $this->substringMatches($vault, $terms, $limit);

            // Nothing contained the term: the caller probably misspelled it.
            if ($contacts->isEmpty()) {
                $contacts = $this->typoMatches($vault, $terms, $limit);
            }

            return ToolResult::json([
                'contacts' => $contacts
                    ->map(fn (Contact $contact) => [
                        'id' => $contact->id,
                        'name' => $this->contactName($author, $contact),
                        'nickname' => $contact->nickname,
                    ])
                    ->values()
                    ->all(),
            ]);
        });
    }

    /**
     * Split a query into the words to match, lowercased.
     *
     * @return array<int, string>
     */
    private function terms(string $query): array
    {
        return array_values(array_filter(
            preg_split('/[\s,]+/', mb_strtolower(trim($query))),
            fn (string $term) => $term !== ''
        ));
    }

    /**
     * Contacts where a name column contains one of the terms.
     *
     * @param  array<int, string>  $terms
     * @return Collection<int, Contact>
     */
    private function substringMatches(Vault $vault, array $terms, int $limit): Collection
    {
        $likeable = array_filter(array_map(fn (string $term) => $this->likeTerm($term), $terms));

        if ($likeable === []) {
            return new Collection;
        }

        $contacts = $vault->contacts()
            ->where(function ($query) use ($likeable) {
                foreach ($likeable as $term) {
                    foreach (self::NAME_COLUMNS as $column) {
                        $query->orWhere($column, 'like', '%'.$term.'%');
                    }
                }
            })
            // Over-fetch so ranking has more than `limit` rows to choose from:
            // "Ann" should not return three Annabels and miss the Ann.
            ->limit(max($limit * 5, 100))
            ->get();

        return $this->rank($contacts, $terms, $limit);
    }

    /**
     * Contacts whose name is within an edit distance of one of the terms.
     *
     * Scanned in chunks rather than loaded whole: fuzzy matching cannot be
     * pushed into the database portably, but it does not need the rows in
     * memory all at once either.
     *
     * @param  array<int, string>  $terms
     * @return Collection<int, Contact>
     */
    private function typoMatches(Vault $vault, array $terms, int $limit): Collection
    {
        $matches = new Collection;

        $vault->contacts()->lazy(self::TYPO_CHUNK)->each(function (Contact $contact) use ($terms, $matches) {
            if ($this->score($contact, $terms) > 0) {
                $matches->push($contact);
            }
        });

        return $this->rank($matches, $terms, $limit);
    }

    /**
     * Order matches best-first and cut to the limit.
     *
     * @param  Collection<int, Contact>  $contacts
     * @param  array<int, string>  $terms
     * @return Collection<int, Contact>
     */
    private function rank(Collection $contacts, array $terms, int $limit): Collection
    {
        return $contacts
            ->sortByDesc(fn (Contact $contact) => $this->score($contact, $terms))
            ->take($limit);
    }

    /**
     * Score a contact against every term, best column wins per term.
     *
     * Exact hits beat prefixes, prefixes beat substrings, and substrings beat
     * typos, so a contact matching two terms always outranks one matching one.
     *
     * @param  array<int, string>  $terms
     */
    private function score(Contact $contact, array $terms): int
    {
        $score = 0;

        foreach ($terms as $term) {
            $best = 0;

            foreach (self::NAME_COLUMNS as $column) {
                $value = mb_strtolower((string) $contact->{$column});

                if ($value === '') {
                    continue;
                }

                $best = max($best, match (true) {
                    $value === $term => 100,
                    str_starts_with($value, $term) => 70,
                    str_contains($value, $term) => 50,
                    default => $this->typoScore($value, $term),
                });
            }

            $score += $best;
        }

        return $score;
    }

    /**
     * Score a near-miss between a name and a term, 0 when they are too far apart.
     *
     * The typo budget scales with the term's length, the way search engines
     * size theirs: a three-letter name has no room for a typo without matching
     * half the vault, while a long one does.
     */
    private function typoScore(string $value, string $term): int
    {
        $budget = match (true) {
            mb_strlen($term) < 5 => 0,
            mb_strlen($term) < 9 => 1,
            default => 2,
        };

        if ($budget === 0) {
            return 0;
        }

        // Compare word by word: "levelace" should match the "Lovelace" in
        // "Ada Lovelace" without being penalised for the rest of the value.
        $best = 0;

        foreach (preg_split('/[\s\-]+/', $value) as $word) {
            $distance = levenshtein($word, $term);

            if ($distance <= $budget) {
                $best = max($best, 30 - ($distance * 5));
            }
        }

        return $best;
    }

    /**
     * Strip LIKE wildcards from a term.
     *
     * Escaping them is not portable — SQLite needs an explicit ESCAPE clause
     * that Laravel's `like` operator does not emit — and no name needs them.
     */
    private function likeTerm(string $term): string
    {
        return str_replace(['%', '_', '\\'], '', $term);
    }
}
