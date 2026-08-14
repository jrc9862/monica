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
        return 'Update the title and body of an existing note. body is required by Monica even on update, so re-send it along with any changes.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact the note belongs to.')
            ->required()
            ->string('note_id')
            ->description('The id of the note to update.')
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
                'contact_id' => $arguments['contact_id'],
                'note_id' => $arguments['note_id'],
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
