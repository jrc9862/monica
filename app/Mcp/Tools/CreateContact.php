<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageContact\Services\CreateContact as CreateContactService;
use App\Mcp\Tools\Concerns\InteractsWithMonica;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

class CreateContact extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Create a new contact in a vault. Only first_name is required; all other fields are optional.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('vault_id')
            ->description('The vault to create the contact in. Required if the user has access to more than one vault.')
            ->optional()
            ->string('first_name')
            ->description('The contact\'s first name.')
            ->optional()
            ->string('last_name')
            ->description('The contact\'s last name.')
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

            $contact = (new CreateContactService)->execute($this->baseData($author) + [
                'vault_id' => $this->resolveVaultId($author, $arguments),
                'first_name' => $arguments['first_name'] ?? null,
                'last_name' => $arguments['last_name'] ?? null,
                'middle_name' => $arguments['middle_name'] ?? null,
                'nickname' => $arguments['nickname'] ?? null,
                'maiden_name' => $arguments['maiden_name'] ?? null,
                'prefix' => $arguments['prefix'] ?? null,
                'suffix' => $arguments['suffix'] ?? null,
                'listed' => true,
            ]);

            return ToolResult::json([
                'id' => $contact->id,
                'name' => $contact->name,
            ]);
        });
    }
}
