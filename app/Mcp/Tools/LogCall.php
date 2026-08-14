<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageCalls\Services\CreateCall;
use App\Mcp\Tools\Concerns\InteractsWithMonica;
use App\Models\Call;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

class LogCall extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Log a phone, video, or in-person call with a contact. Logging an answered call automatically '.
            'creates a 90-day follow-up reminder for that contact.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact the call was with.')
            ->required()
            ->string('called_at')
            ->description('The date the call happened, in Y-m-d format.')
            ->required()
            ->string('type')
            ->description("The kind of call: 'audio', 'video', or 'in_person'.")
            ->required()
            ->string('who_initiated')
            ->description("Who initiated the call: 'me' or 'contact'.")
            ->required()
            ->boolean('answered')
            ->description('Whether the call was answered. Defaults to true. An answered call schedules a 90-day follow-up reminder.')
            ->optional()
            ->integer('duration')
            ->description('Duration of the call, in minutes.')
            ->optional()
            ->string('description')
            ->description('Free-text notes about the call.')
            ->optional()
            ->integer('call_reason_id')
            ->description('The id of the call reason, if any.')
            ->optional()
            ->integer('emotion_id')
            ->description('The id of the emotion felt during the call, if any.')
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

            $call = (new CreateCall)->execute($this->baseData($author) + [
                'vault_id' => $this->resolveVaultId($author, $arguments),
                'contact_id' => $arguments['contact_id'],
                'called_at' => $arguments['called_at'],
                'type' => $arguments['type'],
                'who_initiated' => $arguments['who_initiated'],
                'answered' => $arguments['answered'] ?? true,
                'duration' => $arguments['duration'] ?? null,
                'description' => $arguments['description'] ?? null,
                'call_reason_id' => $arguments['call_reason_id'] ?? null,
                'emotion_id' => $arguments['emotion_id'] ?? null,
            ]);

            return ToolResult::json(['call' => $this->callSummary($call)]);
        });
    }

    private function callSummary(Call $call): array
    {
        return [
            'id' => $call->id,
            'contact_id' => $call->contact_id,
            'called_at' => $call->called_at->toDateString(),
            'type' => $call->type,
            'answered' => $call->answered,
            'who_initiated' => $call->who_initiated,
            'duration' => $call->duration,
            'description' => $call->description,
        ];
    }
}
