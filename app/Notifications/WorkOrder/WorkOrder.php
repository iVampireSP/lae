<?php

namespace App\Notifications\WorkOrder;

use App\Models\WorkOrder\WorkOrder as WorkOrderModel;
use App\Notifications\Channels\WebChannel;
use App\Notifications\Channels\WeComChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
    public function via(): array
    {
        return [WeComChannel::class, WebChannel::class];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(WorkOrderModel $workOrder): array
    {
        $array = $workOrder->toArray();

        $array['latest_reply'] = $workOrder->replies()->latest()->first()?->toArray() ?? [];

        $array['event'] = 'work-order.'.$workOrder->status;

        return $array;
    }

    public function toWeCom(WorkOrderModel $workOrder): false|array
    {
        $workOrder->load(['module', 'user']);

        return [
            'key' => $workOrder->wecom_key,
            'view' => 'notifications.work_order',
            'data' => $workOrder,
        ];
    }
}
