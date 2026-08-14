<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageTasks\Services\ToggleContactTask;
use App\Mcp\Tools\Concerns\InteractsWithMonica;
use App\Models\ContactTask;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

class ToggleTask extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Toggle a task between completed and not completed.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact the task belongs to.')
            ->required()
            ->integer('task_id')
            ->description('The id of the task to toggle.')
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

            $task = (new ToggleContactTask)->execute($this->baseData($author) + [
                'vault_id' => $this->resolveVaultId($author, $arguments),
                'contact_id' => $arguments['contact_id'],
                'contact_task_id' => $arguments['task_id'],
            ]);

            return ToolResult::json(['task' => $this->taskSummary($task)]);
        });
    }

    private function taskSummary(ContactTask $task): array
    {
        return [
            'id' => $task->id,
            'contact_id' => $task->contact_id,
            'label' => $task->label,
            'completed' => $task->completed,
            'completed_at' => $task->completed_at?->toDateTimeString(),
        ];
    }
}
