<?php

namespace Tests\Unit\Domains\Contact\ManageReminders\Services;

use App\Domains\Contact\ManageReminders\Services\SnoozeContactReminder;
use App\Models\ContactReminder;
use App\Models\UserNotificationChannel;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SnoozeContactReminderTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_snoozes_a_reminder_by_7_days(): void
    {
        Carbon::setTestNow(Carbon::create(2024, 1, 1, 12, 0, 0));

        $channel = UserNotificationChannel::factory()->create([
            'type' => UserNotificationChannel::TYPE_TELEGRAM,
            'content' => '123456789',
        ]);
        $reminder = ContactReminder::factory()->create();
        $id = DB::table('contact_reminder_scheduled')->insertGetId([
            'user_notification_channel_id' => $channel->id,
            'contact_reminder_id' => $reminder->id,
            'scheduled_at' => '2024-01-01 09:00:00',
            'triggered_at' => '2024-01-01 09:00:00',
        ]);

        (new SnoozeContactReminder)->execute([
            'contact_reminder_scheduled_id' => $id,
            'period' => '7d',
        ]);

        $this->assertDatabaseHas('contact_reminder_scheduled', [
            'id' => $id,
            'scheduled_at' => '2024-01-08 12:00:00',
            'triggered_at' => null,
        ]);
    }

    /** @test */
    public function it_snoozes_a_reminder_by_14_days(): void
    {
        Carbon::setTestNow(Carbon::create(2024, 1, 1, 12, 0, 0));

        $channel = UserNotificationChannel::factory()->create([
            'type' => UserNotificationChannel::TYPE_TELEGRAM,
            'content' => '123456789',
        ]);
        $reminder = ContactReminder::factory()->create();
        $id = DB::table('contact_reminder_scheduled')->insertGetId([
            'user_notification_channel_id' => $channel->id,
            'contact_reminder_id' => $reminder->id,
            'scheduled_at' => '2024-01-01 09:00:00',
        ]);

        (new SnoozeContactReminder)->execute([
            'contact_reminder_scheduled_id' => $id,
            'period' => '14d',
        ]);

        $this->assertDatabaseHas('contact_reminder_scheduled', [
            'id' => $id,
            'scheduled_at' => '2024-01-15 12:00:00',
            'triggered_at' => null,
        ]);
    }

    /** @test */
    public function it_snoozes_a_reminder_by_30_days(): void
    {
        Carbon::setTestNow(Carbon::create(2024, 1, 1, 12, 0, 0));

        $channel = UserNotificationChannel::factory()->create([
            'type' => UserNotificationChannel::TYPE_TELEGRAM,
            'content' => '123456789',
        ]);
        $reminder = ContactReminder::factory()->create();
        $id = DB::table('contact_reminder_scheduled')->insertGetId([
            'user_notification_channel_id' => $channel->id,
            'contact_reminder_id' => $reminder->id,
            'scheduled_at' => '2024-01-01 09:00:00',
        ]);

        (new SnoozeContactReminder)->execute([
            'contact_reminder_scheduled_id' => $id,
            'period' => '30d',
        ]);

        $this->assertDatabaseHas('contact_reminder_scheduled', [
            'id' => $id,
            'scheduled_at' => '2024-01-31 12:00:00',
            'triggered_at' => null,
        ]);
    }

    /** @test */
    public function it_rejects_an_invalid_period(): void
    {
        $this->expectException(ValidationException::class);

        $channel = UserNotificationChannel::factory()->create([
            'type' => UserNotificationChannel::TYPE_TELEGRAM,
            'content' => '123456789',
        ]);
        $reminder = ContactReminder::factory()->create();
        $id = DB::table('contact_reminder_scheduled')->insertGetId([
            'user_notification_channel_id' => $channel->id,
            'contact_reminder_id' => $reminder->id,
            'scheduled_at' => '2024-01-01 09:00:00',
        ]);

        (new SnoozeContactReminder)->execute([
            'contact_reminder_scheduled_id' => $id,
            'period' => '999d',
        ]);
    }

    /** @test */
    public function it_rejects_a_nonexistent_scheduled_reminder(): void
    {
        $this->expectException(ValidationException::class);

        (new SnoozeContactReminder)->execute([
            'contact_reminder_scheduled_id' => 99999999,
            'period' => '7d',
        ]);
    }
}
