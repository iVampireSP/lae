<?php

namespace App\Notifications;

use App\Events\Users;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommonChannel extends Notification
{
    use Queueable;

    /**
     * Send the given notification.
     *
     * @param mixed        $notifiable
     * @param Notification $notification
     *
     * @return void
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        $data = $notification->toArray($notifiable);

        if (!$data) {
            return;
        }

        $user_id = $notifiable->user_id ?? $notifiable->id;

        $user = User::find($user_id);

        if (!in_array($data['type'] ?? '', ['info', 'success', 'warning', 'error'])) {
            return;
        }

        broadcast(new Users($user, $data['type'], $data));

    }
}
