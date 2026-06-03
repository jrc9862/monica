<?php

namespace App\Domains\Contact\ManageReminders\Services;

use App\Interfaces\ServiceInterface;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SnoozeContactReminder extends BaseService implements ServiceInterface
{
    /**
     * Get the validation rules that apply to the service.
     */
    public function rules(): array
    {
        return [
            'contact_reminder_scheduled_id' => 'required|integer|exists:contact_reminder_scheduled,id',
            'period' => 'required|string|in:7d,14d,30d',
        ];
    }

    /**
     * Get the permissions that apply to the user calling the service.
     */
    public function permissions(): array
    {
        return [];
    }

    /**
     * Postpone a scheduled reminder notification without touching any contact data.
     * The snooze only shifts the notification delivery time forward.
     */
    public function execute(array $data): void
    {
        $this->validateRules($data);

        $newScheduledAt = Carbon::now()->add(match ($data['period']) {
            '7d' => '7 days',
            '14d' => '14 days',
            '30d' => '30 days',
        });

        DB::table('contact_reminder_scheduled')
            ->where('id', $data['contact_reminder_scheduled_id'])
            ->update(['scheduled_at' => $newScheduledAt, 'triggered_at' => null]);
    }
}
