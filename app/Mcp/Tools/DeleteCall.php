<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageCalls\Services\DestroyCall;
use App\Mcp\Tools\Concerns\InteractsWithMonica;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[IsDestructive]
class DeleteCall extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Permanently delete a logged call from a contact.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact the call belongs to.')
            ->required()
            ->integer('call_id')
            ->description('The id of the call to delete.')
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

            (new DestroyCall)->execute($this->baseData($author) + [
                'vault_id' => $this->resolveVaultId($author, $arguments),
                'contact_id' => $arguments['contact_id'],
                'call_id' => $arguments['call_id'],
            ]);

            return ToolResult::json(['deleted' => true]);
        });
    }
}
