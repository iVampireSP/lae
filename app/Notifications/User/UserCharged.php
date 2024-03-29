<?php

namespace App\Notifications\User;

use App\Models\Balance;
use App\Notifications\Channels\WeComChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UserCharged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     */
    public function via(): array
    {
        return [WeComChannel::class];
    }

    public function toWeCom(Balance $notifiable): array
    {
        $notifiable->load('user');

        $user = $notifiable->user;

        $wecom_key = config('settings.wecom.robot_hook.billing');

        return [
            'key' => $wecom_key,
            'view' => 'notifications.charge_success',
            'data' => [
                'user' => $user,
                'balance' => $notifiable,
            ],
        ];
    }
}
