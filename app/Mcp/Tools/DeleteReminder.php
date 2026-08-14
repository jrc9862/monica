<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageReminders\Services\DestroyReminder;
use App\Mcp\Tools\Concerns\InteractsWithMonica;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[IsDestructive]
class DeleteReminder extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Permanently delete a reminder from a contact.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact the reminder belongs to.')
            ->required()
            ->integer('reminder_id')
            ->description('The id of the reminder to delete.')
            ->required()
            ->string('vault_id')
            ->description('The id of the vault the contact belongs to. Required if the user has access to more than one vault.')
            ->optional();
    }

    public function handle(array $arguments): ToolResult
    {
        return $this->guard(function () use ($arguments) {
            $this->ensureTokenCan('write');

            $author = $this->author();

            (new DestroyReminder)->execute($this->baseData($author) + [
                'vault_id' => $this->resolveVaultId($author, $arguments),
                'contact_id' => $arguments['contact_id'],
                'contact_reminder_id' => $arguments['reminder_id'],
            ]);

            return ToolResult::json(['deleted' => true]);
        });
    }
}
