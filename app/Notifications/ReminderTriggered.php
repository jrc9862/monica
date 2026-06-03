<?php

namespace App\Notifications;

use App\Models\UserNotificationChannel;
use App\Models\UserNotificationSent;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class ReminderTriggered extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        private UserNotificationChannel $channel,
        private string $content,
        private string $contactName,
        private ?int $scheduledReminderId = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        switch ($this->channel->type) {
            case UserNotificationChannel::TYPE_EMAIL:
                return ['mail'];
            case UserNotificationChannel::TYPE_TELEGRAM:
                return ['telegram'];
        }

        return [];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        UserNotificationSent::create([
            'user_notification_channel_id' => $this->channel->id,
            'sent_at' => Carbon::now(),
            'subject_line' => $this->content,
        ]);

        return (new MailMessage)
            ->subject(trans('Reminder for :name', ['name' => $this->contactName]))
            ->line(trans('You wanted to be reminded of the following:'))
            ->line($this->content)
            ->line(trans('for'))
            ->line($this->contactName)
            ->line(trans('Test email for Monica'));
    }

    public function toTelegram($notifiable)
    {
        UserNotificationSent::create([
            'user_notification_channel_id' => $this->channel->id,
            'sent_at' => Carbon::now(),
            'subject_line' => $this->content,
        ]);

        $body = "📇 FOLLOW-UP REMINDER\n"
            ."─────────────────────\n"
            ."Contact: {$this->contactName}\n"
            ."Note: {$this->content}";

        $message = TelegramMessage::create()
            ->content($body)
            ->to($this->channel->content);

        if ($this->scheduledReminderId !== null) {
            $id = $this->scheduledReminderId;
            $message
                ->buttonWithCallback('⏩ Snooze 1 week', "snooze:7d:{$id}")
                ->buttonWithCallback('⏩ Snooze 2 weeks', "snooze:14d:{$id}")
                ->buttonWithCallback('⏩ Snooze 1 month', "snooze:30d:{$id}");
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
