<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Common extends Notification implements ShouldQueue
{
    use Queueable;

    public string $title;
    public string $content;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $title, string $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    /**
     * Get the notification's delivery channels.
     *
     *
     * @return array
     */
    public function via(): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     *
     * @return MailMessage
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
     *
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            //
        ];
    }
}
