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
     * @return array
     */
    public function via(): array
    {
        return [WeComChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     *
     * @return MailMessage
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');


    }

    /**
     * Get the array representation of the notification.
     *
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            //
        ];
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

}
