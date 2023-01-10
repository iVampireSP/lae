<?php

namespace App\Notifications;

use App\Models\WorkOrder\WorkOrder as WorkOrderModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrder extends Notification
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
     *
     *
     * @param WorkOrderModel $workOrder
     *
     * @return array
     */
    public function via(WorkOrderModel $workOrder): array
    {
        $methods = [WeComChannel::class, CommonChannel::class];

        if (in_array($workOrder->status, ['processing', 'replied'])) {
            $methods[] = 'mail';
        }

        return $methods;
    }

    /**
     * Get the mail representation of the notification.
     *
     *
     * @param WorkOrderModel $workOrder
     *
     * @return MailMessage
     */
    public function toMail(WorkOrderModel $workOrder): MailMessage
    {
        return (new MailMessage)
            ->subject('工单: ' . $workOrder->title . ' 状态更新。')
            ->line('我们查阅了您的工单并做出了相应处理。')
            ->line('请前往我们的仪表盘继续跟进问题。');
    }

    /**
     * Get the array representation of the notification.
     *
     *
     * @param WorkOrderModel $workOrder
     *
     * @return array
     */
    public function toArray(WorkOrderModel $workOrder): array
    {
        $array = $workOrder->toArray();
        $array['type'] = 'info';
        $array['title'] = '工单: ' . $workOrder->title . ' 状态更新。';

        return $array;
    }

    public function toWeCom(WorkOrderModel $workOrder): false|array
    {
        $workOrder->load(['module', 'user']);

        $module = $workOrder->module;

        if ($workOrder->notify === 0) {
            return false;
        }

        // 取消隐藏字段
        $module->makeVisible(['wecom_key']);

        if ($module->wecom_key == null) {
            $wecom_key = config('settings.wecom.robot_hook.default');
        } else {
            $wecom_key = $module->wecom_key;
        }

        return [
            'key' => $wecom_key,
            'view' => 'notifications.work_order',
            'data' => $workOrder
        ];
    }

    // public function toBroadcast(WorkOrderModel $workOrder): BroadcastMessage
    // {
    //     return new BroadcastMessage($workOrder->toArray());
    // }

}
