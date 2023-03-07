<?php

namespace App\Notifications\User;

use App\Models\User;
use App\Notifications\Channels\WebChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BalanceNotEnough extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     */
    public function via(): array
    {
        return [WebChannel::class, 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $user): MailMessage
    {
        return (new MailMessage)
            ->subject('账户余额不足')
            ->greeting('您好，'.$user->name)
            ->line('账户余额不足')
            ->line('一个或多个主机已被暂停。')
            ->action('查看主机', route('hosts.index'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(): array
    {
        return [
            'title' => '账户余额不足',
            'message' => '被影响的主机已暂停。',
        ];
    }
}
