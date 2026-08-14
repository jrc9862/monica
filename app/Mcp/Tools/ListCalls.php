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
class ListCalls extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'List the most recent calls logged with a contact, newest first.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact whose calls to list.')
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

            $calls = $contact->calls()
                ->orderByDesc('called_at')
                ->limit(20)
                ->get()
                ->map(fn ($call) => [
                    'id' => $call->id,
                    'called_at' => $call->called_at->toDateString(),
                    'answered' => $call->answered,
                    'who_initiated' => $call->who_initiated,
                    'type' => $call->type,
                    'description' => $call->description ? Str::limit($call->description, 140) : null,
                ])
                ->values()
                ->all();

            return ToolResult::json(['calls' => $calls]);
        });
    }
}
