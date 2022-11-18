<?php

namespace App\Notifications;

use App\Broadcasting\WeComRobotChannel;
use App\Models\Balance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserBalanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    // public function via($notifiable)
    // {
    //     // return [WeComRobotChannel::class];
    // }

    public function toGroup($notifiable)
    {
        if ($notifiable instanceof Balance) {

            if ($notifiable->paid_at !== null) {
                $view = 'notifications.user.balance';
                $notifiable->load('user');
                $user = $notifiable->user;


                $wecom_key = config('settings.wecom.robot_hook.billing');


                $data = [
                    'balance' => $notifiable,
                    'user' => $user,
                ];

                $resp = Http::post('https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=' . $wecom_key, [
                    'msgtype' => 'markdown',
                    'markdown' => [
                        'content' => view($view, $data)->render(),
                    ],
                ]);

                if (!$resp->successful()) {
                    Log::error('企业微信机器人发送失败', $data);
                }
            }
        }
    }
}
