<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageNotes\Services\CreateNote as CreateNoteService;
use App\Mcp\Tools\Concerns\InteractsWithMonica;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

class CreateNote extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Add a new note to a contact. Notes are free-form text entries used to record observations about a contact.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact to attach the note to.')
            ->required()
            ->string('vault_id')
            ->description('The vault the contact belongs to. Required if the user has access to more than one vault.')
            ->optional()
            ->string('body')
            ->description('The content of the note.')
            ->required()
            ->string('title')
            ->description('An optional title for the note.')
            ->optional();
    }

    public function handle(array $arguments): ToolResult
    {
        return $this->guard(function () use ($arguments) {
            $this->ensureTokenCan('write');

            $author = $this->author();

            $note = (new CreateNoteService)->execute($this->baseData($author) + [
                'vault_id' => $this->resolveVaultId($author, $arguments),
                'contact_id' => $arguments['contact_id'],
                'title' => $arguments['title'] ?? null,
                'body' => $arguments['body'],
            ]);

            return ToolResult::json([
                'id' => $note->id,
                'title' => $note->title,
            ]);
        });
    }
}
