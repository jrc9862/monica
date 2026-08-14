<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithMonica;
use App\Models\Vault;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[IsReadOnly]
class ListReminders extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'List the reminders set for a contact (birthdays, follow-ups, and other recurring or one-time reminders).';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact whose reminders to list.')
            ->required()
            ->string('vault_id')
            ->description('The id of the vault the contact belongs to. Required if the user has access to more than one vault.')
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

            $reminders = $contact->reminders()
                ->orderBy('month')
                ->orderBy('day')
                ->limit(50)
                ->get()
                ->map(fn ($reminder) => [
                    'id' => $reminder->id,
                    'label' => $reminder->label,
                    'day' => $reminder->day,
                    'month' => $reminder->month,
                    'year' => $reminder->year,
                    'type' => $reminder->type,
                ])
                ->values()
                ->all();

            return ToolResult::json(['reminders' => $reminders]);
        });
    }
}
