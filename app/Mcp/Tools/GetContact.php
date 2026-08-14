<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithMonica;
use App\Models\Vault;
use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[IsReadOnly]
class GetContact extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Get the full details of a single contact, including recent notes and counts of calls, reminders and tasks. Use search-contacts first to find the contact_id.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact to fetch.')
            ->required()
            ->string('vault_id')
            ->description('The vault the contact belongs to. Required if the user has access to more than one vault.')
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

            $contact = $vault->contacts()->findOrFail($arguments['contact_id']);

            $notes = $contact->notes()
                ->latest('created_at')
                ->take(10)
                ->get()
                ->map(fn ($note) => [
                    'id' => $note->id,
                    'title' => $note->title,
                    'excerpt' => Str::limit($note->body, 200),
                ])
                ->values()
                ->all();

            return ToolResult::json([
                'id' => $contact->id,
                'name' => $contact->name,
                'nickname' => $contact->nickname,
                'job_position' => $contact->job_position,
                'company' => $contact->company?->name,
                'last_updated_at' => optional($contact->last_updated_at)->toIso8601String(),
                'notes' => $notes,
                'counts' => [
                    'calls' => $contact->calls()->count(),
                    'reminders' => $contact->reminders()->count(),
                    'tasks' => $contact->tasks()->count(),
                ],
            ]);
        });
    }
}
