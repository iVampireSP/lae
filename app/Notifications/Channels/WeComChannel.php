<?php

namespace App\Notifications\Channels;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeComChannel extends Notification
{
    use Queueable;

    /**
     * Send the given notification.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        $data = $notification->toWeCom($notifiable);

        if (! $data) {
            return;
        }

        $view = $data['view'];
        $key = $data['wecom_key'] ?? null;

        if (! $key) {
            $key = config('settings.wecom.robot_hook.default');
        }

        $resp = Http::post('https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key='.$key, [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => view($view, [
                    'data' => $data['data'],
                ])->render(),
            ],
        ]);

        if (! $resp->successful()) {
            Log::error('企业微信机器人发送失败', $data['data']);
        }
    }
}
