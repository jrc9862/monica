<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageNotes\Services\DestroyNote;
use App\Mcp\Tools\Concerns\InteractsWithMonica;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[IsDestructive]
class DeleteNote extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Permanently delete a note from a contact. This cannot be undone; confirm with the user before calling.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact the note belongs to.')
            ->required()
            ->string('note_id')
            ->description('The id of the note to delete.')
            ->required()
            ->string('vault_id')
            ->description('The vault the contact belongs to. Required if the user has access to more than one vault.')
            ->optional();
    }

    public function handle(array $arguments): ToolResult
    {
        return $this->guard(function () use ($arguments) {
            $this->ensureTokenCan('write');

            $author = $this->author();

            (new DestroyNote)->execute($this->baseData($author) + [
                'vault_id' => $this->resolveVaultId($author, $arguments),
                'contact_id' => $arguments['contact_id'],
                'note_id' => $arguments['note_id'],
            ]);

            return ToolResult::json(['deleted' => true, 'note_id' => $arguments['note_id']]);
        });
    }
}
