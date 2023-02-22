<?php

namespace App\Notifications\User;

use App\Notifications\Channels\WebChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $title;

    public string $content;

    public bool $send_mail;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $title, string $content, bool $send_mail = false)
    {
        $this->title = $title;
        $this->content = $content;
        $this->send_mail = $send_mail;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(): array
    {
        $channels = [WebChannel::class];

        if ($this->send_mail) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage)->subject($this->title)->markdown('mail.common', [
            'title' => $this->title,
            'content' => $this->content,
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'event' => 'notifications',
        ];
    }

    public function viaQueues(): array
    {
        return [
            WebChannel::class => 'notifications',
            'mail' => 'notifications',
        ];
    }
}
