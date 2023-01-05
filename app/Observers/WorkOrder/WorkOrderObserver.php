<?php

namespace App\Observers\WorkOrder;

use App\Models\WorkOrder\WorkOrder;
use App\Notifications\WorkOrderNotification;

class WorkOrderObserver
{
    /**
     * Handle the WorkOrder "created" event.
     *
     * @param WorkOrder $workOrder
     *
     * @return void|null
     */
    public function created(WorkOrder $workOrder)
    {
        (new WorkOrderNotification())
            ->toGroup($workOrder);
    }

    /**
     * Handle the WorkOrder "updated" event.
     *
     * @param WorkOrder $workOrder
     *
     * @return void|null
     */
    public function updated(WorkOrder $workOrder)
    {
        (new WorkOrderNotification())
            ->toGroup($workOrder);
    }

    /**
     * Handle the WorkOrder "deleted" event.
     *
     * @param WorkOrder $workOrder
     *
     * @return void
     */
    public function deleted(WorkOrder $workOrder): void
    {
        //
    }

    /**
     * Handle the WorkOrder "restored" event.
     *
     * @param WorkOrder $workOrder
     *
     * @return void
     */
    public function restored(WorkOrder $workOrder): void
    {
        //
    }

}
