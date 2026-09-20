<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageNotes\Services\UpdateNote as UpdateNoteService;
use App\Mcp\Tools\Concerns\InteractsWithMonica;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[IsIdempotent]
class UpdateNote extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Update the title and body of an existing note. This replaces the note rather than patching it: '
            .'omitting title clears the existing title. Read the note with list-notes first and send back both '
            .'fields, editing only what you mean to change. body is required by Monica even when you are only '
            .'changing the title.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact the note belongs to.')
            ->required()
            ->integer('note_id')
            ->description('The id of the note to update, as returned by list-notes.')
            ->required()
            ->string('vault_id')
            ->description('The vault the contact belongs to. Required if the user has access to more than one vault.')
            ->optional()
            ->string('body')
            ->description('The content of the note (required).')
            ->required()
            ->string('title')
            ->optional();
    }

    public function handle(array $arguments): ToolResult
    {
        return $this->guard(function () use ($arguments) {
            $this->ensureTokenCan('write');

            $author = $this->author();

            $note = (new UpdateNoteService)->execute($this->baseData($author) + [
                'vault_id' => $this->resolveVaultId($author, $arguments),
                'contact_id' => $this->required($arguments, 'contact_id'),
                'note_id' => $this->required($arguments, 'note_id'),
                'title' => $arguments['title'] ?? null,
                'body' => $this->required($arguments, 'body'),
            ]);

            return ToolResult::json([
                'id' => $note->id,
                'title' => $note->title,
            ]);
        });
    }
}
