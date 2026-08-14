<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithMonica;
use App\Models\Vault;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[IsReadOnly]
class ListTasks extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'List the tasks for a contact, including whether each one is completed.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact whose tasks to list.')
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

            $tasks = $contact->tasks()
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($task) => [
                    'id' => $task->id,
                    'label' => $task->label,
                    'completed' => $task->completed,
                    'completed_at' => $task->completed_at?->toDateTimeString(),
                    'due_at' => $task->due_at?->toDateTimeString(),
                ])
                ->values()
                ->all();

            return ToolResult::json(['tasks' => $tasks]);
        });
    }
}
