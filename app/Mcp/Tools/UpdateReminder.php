<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageReminders\Services\UpdateContactReminder;
use App\Mcp\Tools\Concerns\InteractsWithMonica;
use App\Models\ContactReminder;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[IsIdempotent]
class UpdateReminder extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'Update an existing reminder for a contact. All fields are replaced with the given values.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('contact_id')
            ->description('The id of the contact this reminder is about.')
            ->required()
            ->integer('reminder_id')
            ->description('The id of the reminder to update.')
            ->required()
            ->string('label')
            ->description('A short description of the reminder, e.g. "Birthday".')
            ->required()
            ->string('type')
            ->description("The reminder type: 'one_time', 'recurring_day', 'recurring_month', or 'recurring_year'.")
            ->required()
            ->integer('day')
            ->description('Day of the month the reminder occurs on.')
            ->optional()
            ->integer('month')
            ->description('Month the reminder occurs in.')
            ->optional()
            ->integer('year')
            ->description('Year of the reminder, required for one-time reminders.')
            ->optional()
            ->integer('frequency_number')
            ->description('For recurring reminders, how often the type unit repeats (e.g. every 2 years).')
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

            $reminder = (new UpdateContactReminder)->execute($this->baseData($author) + [
                'vault_id' => $this->resolveVaultId($author, $arguments),
                'contact_id' => $arguments['contact_id'],
                'contact_reminder_id' => $arguments['reminder_id'],
                'label' => $arguments['label'],
                'type' => $arguments['type'],
                'day' => $arguments['day'] ?? null,
                'month' => $arguments['month'] ?? null,
                'year' => $arguments['year'] ?? null,
                'frequency_number' => $arguments['frequency_number'] ?? null,
            ]);

            return ToolResult::json(['reminder' => $this->reminderSummary($reminder)]);
        });
    }

    private function reminderSummary(ContactReminder $reminder): array
    {
        return [
            'id' => $reminder->id,
            'contact_id' => $reminder->contact_id,
            'label' => $reminder->label,
            'day' => $reminder->day,
            'month' => $reminder->month,
            'year' => $reminder->year,
            'type' => $reminder->type,
        ];
    }
}
