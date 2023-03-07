<?php

namespace App\Notifications\User;

use App\Models\User;
use App\Notifications\Channels\WebChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowBalance extends Notification
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
            ->subject('账户余额过低')
            ->line('您的账户余额过低。还剩下 '.$user->balance.' 元。')
            ->action('充值', route('balances.index'))
            ->line('当您的账户欠费时，您的服务将会被暂停。');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(User $user): array
    {
        return [
            'title' => '账户余额过低',
            'message' => '您的账户余额过低。还剩下'.$user->balance.'元。当您的账户欠费时，您的服务将会被暂停。',
        ];
    }
}
