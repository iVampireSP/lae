<?php

namespace App\Notifications;

use App\Notifications\Channels\WebChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class WebNotification extends Notification
{
    use Queueable;

    public array|Model $message = [];
    public string $event = 'notification';

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array|Model $message, string $event)
    {
        if ($message instanceof Model) {
            $message = $message->toArray();
        }

        $this->message = $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(): array
    {
        return [WebChannel::class];
    }
}
