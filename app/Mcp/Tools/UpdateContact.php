<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageContact\Services\UpdateContact as UpdateContactService;
use App\Mcp\Tools\Concerns\InteractsWithMonica;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[IsIdempotent]
class UpdateContact extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Update an existing contact\'s name fields. first_name is required by Monica even on update, so it is re-sent along with any changed fields.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact to update.')
            ->required()
            ->string('vault_id')
            ->description('The vault the contact belongs to. Required if the user has access to more than one vault.')
            ->optional()
            ->string('first_name')
            ->description('The contact\'s first name (required).')
            ->required()
            ->string('last_name')
            ->optional()
            ->string('middle_name')
            ->optional()
            ->string('nickname')
            ->optional()
            ->string('maiden_name')
            ->optional()
            ->string('prefix')
            ->optional()
            ->string('suffix')
            ->optional();
    }

    public function handle(array $arguments): ToolResult
    {
        return $this->guard(function () use ($arguments) {
            $this->ensureTokenCan('write');

            $author = $this->author();

            $contact = (new UpdateContactService)->execute($this->baseData($author) + [
                'vault_id' => $this->resolveVaultId($author, $arguments),
                'contact_id' => $arguments['contact_id'],
                'first_name' => $arguments['first_name'],
                'last_name' => $arguments['last_name'] ?? null,
                'middle_name' => $arguments['middle_name'] ?? null,
                'nickname' => $arguments['nickname'] ?? null,
                'maiden_name' => $arguments['maiden_name'] ?? null,
                'prefix' => $arguments['prefix'] ?? null,
                'suffix' => $arguments['suffix'] ?? null,
            ]);

            return ToolResult::json([
                'id' => $contact->id,
                'name' => $contact->name,
            ]);
        });
    }
}
