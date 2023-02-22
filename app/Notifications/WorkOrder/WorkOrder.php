<?php

namespace App\Notifications\WorkOrder;

use App\Models\WorkOrder\WorkOrder as WorkOrderModel;
use App\Notifications\Channels\WebChannel;
use App\Notifications\Channels\WeComChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrder extends Notification implements ShouldQueue
{
    use Queueable;

    public WorkOrderModel $work_order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(WorkOrderModel $work_order)
    {
        $this->work_order = $work_order;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(WorkOrderModel $workOrder): array
    {
        $methods = [WeComChannel::class, WebChannel::class];

        if (in_array($workOrder->status, ['processing', 'replied'])) {
            $methods[] = 'mail';
        }

        return $methods;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(WorkOrderModel $workOrder): MailMessage
    {
        return (new MailMessage)
            ->subject('工单: '.$workOrder->title.' 状态更新。')
            ->line('我们查阅了您的工单并做出了相应处理。')
            ->line('请前往我们的仪表盘继续跟进问题。');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(WorkOrderModel $workOrder): array
    {
        $array = $workOrder->toArray();

        $array['event'] = 'work-order.'.$workOrder->status;

        return $array;
    }

    public function toWeCom(WorkOrderModel $workOrder): false|array
    {
        $workOrder->load(['module', 'user']);

        $wecom_key = config('settings.wecom.robot_hook.default');

        if ($workOrder->module) {
            $module = $workOrder->module;

            $wecom_key = $module->makeVisible(['wecom_key'])->wecom_key ?? $wecom_key;
        }

        return [
            'key' => $wecom_key,
            'view' => 'notifications.work_order',
            'data' => $workOrder,
        ];
    }

    // public function toBroadcast(WorkOrderModel $workOrder): BroadcastMessage
    // {
    //     return new BroadcastMessage($workOrder->toArray());
    // }
}
