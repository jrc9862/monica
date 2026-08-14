<?php

namespace Tests\Feature\Controllers;

use App\Domains\Settings\ManageNotificationChannels\Web\Controllers\TelegramWebhookController;
use App\Models\ContactReminder;
use App\Models\UserNotificationChannel;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TelegramWebhookControllerTest extends TestCase
{
    use DatabaseTransactions;

    private string $url = '/telegram/webhook/testhook';

    protected function setUp(): void
    {
        parent::setUp();

        // The real route is only registered when the telegram token config is set,
        // and that resolution happens at boot — before test config changes take effect.
        // Register the route manually so it resolves deterministically. CSRF is already
        // excluded for /telegram/webhook/* in bootstrap/app.php.
        Route::post($this->url, [TelegramWebhookController::class, 'store']);
    }

    private function scheduledRow(UserNotificationChannel $channel, ?string $triggeredAt = '2024-01-01 09:00:00'): int
    {
        $reminder = ContactReminder::factory()->create();

        return DB::table('contact_reminder_scheduled')->insertGetId([
            'user_notification_channel_id' => $channel->id,
            'contact_reminder_id' => $reminder->id,
            'scheduled_at' => '2024-01-01 09:00:00',
            'triggered_at' => $triggeredAt,
        ]);
    }

    // ---------------------------------------------------------------------
    // handleMessage / verification flow
    // ---------------------------------------------------------------------

    /** @test */
    public function it_verifies_a_channel_with_a_valid_start_token(): void
    {
        $channel = UserNotificationChannel::factory()->create([
            'type' => UserNotificationChannel::TYPE_TELEGRAM,
            'content' => 'unverified',
            'active' => false,
            'verification_token' => 'abcdefgh-1234-5678-9012-abcdefabcdef',
        ]);

        $response = $this->postJson($this->url, [
            'message' => [
                'text' => '/start abcdefgh-1234-5678-9012-abcdefabcdef',
                'chat' => ['id' => 987654321],
            ],
        ]);

        $response->assertStatus(200);

        $channel->refresh();
        $this->assertEquals('987654321', $channel->content);
        $this->assertTrue($channel->active);
    }

    /** @test */
    public function it_returns_404_for_an_unknown_verification_token(): void
    {
        $response = $this->postJson($this->url, [
            'message' => [
                'text' => '/start 00000000-0000-0000-0000-000000000000',
                'chat' => ['id' => 987654321],
            ],
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_202_for_text_not_matching_the_start_pattern(): void
    {
        $channel = UserNotificationChannel::factory()->create([
            'type' => UserNotificationChannel::TYPE_TELEGRAM,
            'content' => 'unverified',
            'active' => false,
            'verification_token' => 'abcdefgh-1234-5678-9012-abcdefabcdef',
        ]);

        $response = $this->postJson($this->url, [
            'message' => [
                'text' => 'hello',
                'chat' => ['id' => 987654321],
            ],
        ]);

        $response->assertStatus(202);

        $channel->refresh();
        $this->assertEquals('unverified', $channel->content);
        $this->assertFalse($channel->active);
    }

    /** @test */
    public function it_returns_202_when_message_has_no_text_key(): void
    {
        // Regression: a message object without a "text" key must not throw (old dead try/catch).
        $response = $this->postJson($this->url, [
            'message' => [
                'chat' => ['id' => 987654321],
            ],
        ]);

        $response->assertStatus(202);
    }

    /** @test */
    public function it_returns_202_when_message_is_absent(): void
    {
        $response = $this->postJson($this->url, []);

        $response->assertStatus(202);
    }

    /** @test */
    public function it_acks_a_start_message_missing_the_chat_id(): void
    {
        // Regression: a valid /start token without a chat id must not crash on the
        // NOT NULL content column when saving; it should ack with 202 instead.
        $channel = UserNotificationChannel::factory()->create([
            'type' => UserNotificationChannel::TYPE_TELEGRAM,
            'content' => 'unverified',
            'active' => false,
            'verification_token' => 'abcdefgh-1234-5678-9012-abcdefabcdef',
        ]);

        $response = $this->postJson($this->url, [
            'message' => [
                'text' => '/start abcdefgh-1234-5678-9012-abcdefabcdef',
            ],
        ]);

        $response->assertStatus(202);

        $channel->refresh();
        $this->assertEquals('unverified', $channel->content);
        $this->assertFalse($channel->active);
    }

    // ---------------------------------------------------------------------
    // handleCallbackQuery / snooze flow
    // ---------------------------------------------------------------------

    /** @test */
    public function it_snoozes_a_reminder_by_7_days_on_matching_chat_id(): void
    {
        Carbon::setTestNow(Carbon::create(2024, 1, 1, 12, 0, 0));

        $channel = UserNotificationChannel::factory()->create([
            'type' => UserNotificationChannel::TYPE_TELEGRAM,
            'content' => '123456789',
        ]);
        $id = $this->scheduledRow($channel);

        $response = $this->postJson($this->url, [
            'callback_query' => [
                'data' => "snooze:7d:{$id}",
                'message' => ['chat' => ['id' => 123456789]],
            ],
        ]);

        $response->assertStatus(200);

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
        $id = $this->scheduledRow($channel);

        $response = $this->postJson($this->url, [
            'callback_query' => [
                'data' => "snooze:14d:{$id}",
                'message' => ['chat' => ['id' => 123456789]],
            ],
        ]);

        $response->assertStatus(200);

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
        $id = $this->scheduledRow($channel);

        $response = $this->postJson($this->url, [
            'callback_query' => [
                'data' => "snooze:30d:{$id}",
                'message' => ['chat' => ['id' => 123456789]],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('contact_reminder_scheduled', [
            'id' => $id,
            'scheduled_at' => '2024-01-31 12:00:00',
            'triggered_at' => null,
        ]);
    }

    /** @test */
    public function it_returns_202_for_callback_data_not_matching_the_snooze_pattern(): void
    {
        $channel = UserNotificationChannel::factory()->create([
            'type' => UserNotificationChannel::TYPE_TELEGRAM,
            'content' => '123456789',
        ]);
        $id = $this->scheduledRow($channel);

        $response = $this->postJson($this->url, [
            'callback_query' => [
                'data' => "snooze:99d:{$id}",
                'message' => ['chat' => ['id' => 123456789]],
            ],
        ]);

        $response->assertStatus(202);

        $this->assertDatabaseHas('contact_reminder_scheduled', [
            'id' => $id,
            'scheduled_at' => '2024-01-01 09:00:00',
        ]);
    }

    /** @test */
    public function it_returns_404_for_an_unknown_scheduled_id(): void
    {
        $response = $this->postJson($this->url, [
            'callback_query' => [
                'data' => 'snooze:7d:999999',
                'message' => ['chat' => ['id' => 123456789]],
            ],
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_403_when_chat_id_does_not_match_the_channel_content(): void
    {
        $channel = UserNotificationChannel::factory()->create([
            'type' => UserNotificationChannel::TYPE_TELEGRAM,
            'content' => '123456789',
        ]);
        $id = $this->scheduledRow($channel);

        $response = $this->postJson($this->url, [
            'callback_query' => [
                'data' => "snooze:7d:{$id}",
                'message' => ['chat' => ['id' => 555555555]],
            ],
        ]);

        $response->assertStatus(403);

        // Row untouched: scheduled_at and triggered_at unchanged.
        $this->assertDatabaseHas('contact_reminder_scheduled', [
            'id' => $id,
            'scheduled_at' => '2024-01-01 09:00:00',
            'triggered_at' => '2024-01-01 09:00:00',
        ]);
    }

    /** @test */
    public function it_does_not_500_when_callback_message_chat_id_is_missing(): void
    {
        $channel = UserNotificationChannel::factory()->create([
            'type' => UserNotificationChannel::TYPE_TELEGRAM,
            'content' => '123456789',
        ]);
        $id = $this->scheduledRow($channel);

        $response = $this->postJson($this->url, [
            'callback_query' => [
                'data' => "snooze:7d:{$id}",
            ],
        ]);

        // Missing chat id resolves to '' which cannot match the channel content → 403, not 500.
        $this->assertNotEquals(500, $response->getStatusCode());
        $response->assertStatus(403);
    }
}
