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

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array|Model $message, string $event = 'notification')
    {
        if ($message instanceof Model) {
            $this->message = $message->toArray();
        } else {
            $this->message = $message;
        }

        $this->message['event'] = $event;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(): array
    {
        return $this->message;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(): array
    {
        return [WebChannel::class];
    }

    public function viaQueues(): array
    {
        return [
            WebChannel::class => 'notifications',
        ];
    }
}
