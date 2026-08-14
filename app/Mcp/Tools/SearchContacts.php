<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithMonica;
use App\Models\Contact;
use App\Models\Vault;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[IsReadOnly]
class SearchContacts extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Search for contacts by name (first, last, middle, nickname, maiden name) within a vault. Use this to find a contact before reading or editing their details.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('query')
            ->description('The search term to match against contact names.')
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

            $contacts = Contact::search($arguments['query'])
                ->where('vault_id', $vault->id)
                ->take($limit)
                ->get()
                ->map(fn (Contact $contact) => [
                    'id' => $contact->id,
                    'name' => trim($contact->first_name.' '.$contact->last_name),
                    'nickname' => $contact->nickname,
                ])
                ->values()
                ->all();

            return ToolResult::json(['contacts' => $contacts]);
        });
    }
}
