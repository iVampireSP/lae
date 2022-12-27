<?php

namespace App\Notifications;

use App\Models\Module;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
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
     * @param mixed $notifiable
     *
     * @return void
     */
    public function toGroup($notifiable)
    {
        $module = $this->module;

        // make wecom_key visible
        $wecom_key = $module->wecom_key ?? config('settings.wecom.robot_hook.billing');


        $text = "# {$module->name} 收益";
        foreach ($notifiable as $year => $months) {
            // 排序 months 从小到大
            ksort($months);

            $total = 0;
            $total_should = 0;

            foreach ($months as $month => $m) {
                $total += round($m['balance'], 2);
                $total_should += round($m['should_balance'], 2);
                $text .= <<<EOF

==========
{$year}年 {$month}月
实收: {$total}元
应得: {$total_should} 元

EOF;
            }
        }


        $resp = Http::post('https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=' . $wecom_key, [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => $text,
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
