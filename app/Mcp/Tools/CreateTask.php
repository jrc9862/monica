<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageTasks\Services\CreateContactTask;
use App\Mcp\Tools\Concerns\InteractsWithMonica;
use App\Models\ContactTask;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

class CreateTask extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Create a task for a contact, optionally with a due date.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact this task is about.')
            ->required()
            ->string('label')
            ->description('The task title.')
            ->required()
            ->string('description')
            ->description('Extra detail about the task.')
            ->optional()
            ->string('due_at')
            ->description('When the task is due, as a date/time string.')
            ->optional()
            ->string('vault_id')
            ->description('The id of the vault the contact belongs to. Required if the user has access to more than one vault.')
            ->optional();
    }

    public function handle(array $arguments): ToolResult
    {
        return $this->guard(function () use ($arguments) {
            $this->ensureTokenCan('write');

            $author = $this->author();

            $task = (new CreateContactTask)->execute($this->baseData($author) + [
                'vault_id' => $this->resolveVaultId($author, $arguments),
                'contact_id' => $arguments['contact_id'],
                'label' => $arguments['label'],
                'description' => $arguments['description'] ?? null,
                'due_at' => $arguments['due_at'] ?? null,
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
            'description' => $task->description,
            'completed' => $task->completed,
            'due_at' => $task->due_at?->toDateTimeString(),
        ];
    }
}
