<?php

namespace App\Domains\Settings\ManageNotificationChannels\Web\Controllers;

use App\Domains\Contact\ManageReminders\Services\SnoozeContactReminder;
use App\Domains\Settings\ManageNotificationChannels\Services\ScheduleAllContactRemindersForNotificationChannel;
use App\Http\Controllers\Controller;
use App\Models\UserNotificationChannel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TelegramWebhookController extends Controller
{
    /**
     * Handle incoming Telegram webhook updates.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->has('callback_query')) {
            return $this->handleCallbackQuery($request);
        }

        return $this->handleMessage($request);
    }

    /**
     * Handle inline keyboard callback_query (snooze buttons).
     *
     * @return \Illuminate\Http\Response
     */
    private function handleCallbackQuery(Request $request)
    {
        $callbackQuery = $request->callback_query;

        if (! is_array($callbackQuery)) {
            return response('Accepted', 202);
        }

        $callbackData = $callbackQuery['data'] ?? '';
        $chatId = (string) ($callbackQuery['message']['chat']['id'] ?? '');

        // expected format: snooze:{period}:{scheduled_id}
        if (! \Safe\preg_match('/^snooze:(7d|14d|30d):(\d+)$/', $callbackData, $matches)) {
            return response('Accepted', 202);
        }

        [, $period, $scheduledId] = $matches;

        // Verify the chat ID matches the channel that owns this scheduled reminder
        $scheduledReminder = DB::table('contact_reminder_scheduled')
            ->where('id', (int) $scheduledId)
            ->first();

        if (! $scheduledReminder) {
            return response('Not found', 404);
        }

        $channel = UserNotificationChannel::find($scheduledReminder->user_notification_channel_id);

        if (! $channel || $channel->content !== $chatId) {
            return response('Forbidden', 403);
        }

        try {
            (new SnoozeContactReminder)->execute([
                'contact_reminder_scheduled_id' => (int) $scheduledId,
                'period' => $period,
            ]);
        } catch (Exception $e) {
            return response('Error: '.$e->getMessage(), 500);
        }

        return response('OK', 200);
    }

    /**
     * Handle incoming text messages (verification flow).
     *
     * @return \Illuminate\Http\Response
     */
    private function handleMessage(Request $request)
    {
        $messagePayload = $request->message;
        $messageText = is_array($messagePayload) ? ($messagePayload['text'] ?? null) : null;

        if (! is_string($messageText)) {
            // No usable text (e.g. a non-text update); ack with 202 so Telegram stops retrying.
            return response('Accepted', 202);
        }

        // check if the message matches the expected pattern.
        // if the message does not match the pattern, then we return a 202 response
        // so telegram will stop trying to send the message.
        $message = Str::of($messageText);
        if (! $message->test('/^\/start\s[A-Za-z0-9-]{36}$/')) {
            return response('Accepted', 202);
        }

        // Cleanup the string
        $verificationKey = $message->remove('/start ')->rtrim();

        // Get Telegram ID from the request.
        $chatId = $messagePayload['chat']['id'] ?? null;

        if (! is_int($chatId) && ! is_string($chatId)) {
            // No usable chat id; ack with 202 so Telegram stops retrying instead of
            // crashing on the NOT NULL content column.
            return response('Accepted', 202);
        }

        // Get the User ID from the cache using the temp code as key.
        try {
            $channel = UserNotificationChannel::where('verification_token', $verificationKey)->firstOrFail();
        } catch (Exception) {
            return response('Error', 404);
        }

        // Update user with the Telegram Chat ID
        $channel->content = (string) $chatId;
        $channel->active = true;
        $channel->save();

        (new ScheduleAllContactRemindersForNotificationChannel)->execute([
            'account_id' => $channel->user->account_id,
            'author_id' => $channel->user->id,
            'user_notification_channel_id' => $channel->id,
        ]);

        return response('Success', 200);
    }
}
