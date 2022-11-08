<?php

namespace App\Notifications;

use App\Broadcasting\WeComRobotChannel;
use App\Models\WorkOrder\Reply;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorkOrderNotification extends Notification implements ShouldQueue
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
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [];
    }

    public function toGroup($notifiable)
    {
        $workOrder = $notifiable;

        $reply = [];

        if ($notifiable instanceof WorkOrder) {

            $view = 'notifications.work_order.created';

            $workOrder->load(['module', 'user']);

            $module = $workOrder->module;
        } elseif ($notifiable instanceof Reply) {

            $view = 'notifications.work_order.reply';


            $workOrder->load(['workOrder', 'user']);
            $workOrder->workOrder->load('module');

            $reply = $workOrder;
            $workOrder = $workOrder->workOrder;

            $module = $workOrder->module;
        } else {
            return;
        }

        // 取消隐藏字段
        $module->makeVisible(['wecom_key']);

        if ($module->wecom_key == null) {
            $wecom_key = config('settings.wecom.robot_hook.default');
        } else {
            $wecom_key = $module->wecom_key;
        }

        // 隐藏字段
        $module->makeHidden(['wecom_key']);


        $resp = Http::post('https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=' . $wecom_key, [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => view($view, [
                    'workOrder' => $workOrder,
                    'user' => $workOrder->user,
                    'reply' => $reply,
                    'module' => $module,
                ])->render(),
            ],
        ]);

        if (!$resp->successful()) {
            Log::error('企业微信机器人发送失败', [
                'resp' => $resp->json(),
                'workOrder' => $workOrder,
                'reply' => $reply,
                'module' => $module,
            ]);
        }
    }
}
