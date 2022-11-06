<?php

namespace App\Notifications;

use App\Models\Module;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class ModuleEarnings extends Notification
{
    use Queueable;

    protected Module $module;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toGroup($notifiable)
    {
        if (!isset($notifiable['transactions'])) {
            return;
        }

        $module = $this->module;

        $view = 'notifications.module.earnings';

        // make wecom_key visible
        $wecom_key = $module->wecom_key ?? config('settings.wecom.robot_hook.billing');

        $resp = Http::post('https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=' . $wecom_key, [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => view($view, [
                    'module' => $module,
                    'data' => $notifiable,
                ])->render(),
            ],
        ]);

        if ($resp->failed()) {
            Log::error('发送模块盈利到企业微信时失败', [
                'module' => $module->id,
                'data' => $notifiable,
                'resp' => $resp->json(),
            ]);
        }
    }
}
