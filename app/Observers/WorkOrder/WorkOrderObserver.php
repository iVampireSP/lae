<?php

namespace App\Observers\WorkOrder;

use App\Models\WorkOrder\WorkOrder;
use App\Notifications\WorkOrderNotification;
use Illuminate\Support\Facades\Log;

class WorkOrderObserver
{
    /**
     * Handle the WorkOrder "created" event.
     *
     * @param  \App\Models\WorkOrder\WorkOrder  $workOrder
     * @return void
     */
    public function created(WorkOrder $workOrder)
    {
        //
        return (new WorkOrderNotification())
            ->toGroup($workOrder);
    }

    /**
     * Handle the WorkOrder "updated" event.
     *
     * @param  \App\Models\WorkOrder\WorkOrder  $workOrder
     * @return void
     */
    public function updated(WorkOrder $workOrder)
    {

        Log::debug('workOrder updated', ['workOrder' => $workOrder]);
        //
        return (new WorkOrderNotification())
            ->toGroup($workOrder);
    }

    /**
     * Handle the WorkOrder "deleted" event.
     *
     * @param  \App\Models\WorkOrder\WorkOrder  $workOrder
     * @return void
     */
    public function deleted(WorkOrder $workOrder)
    {
        //
    }

    /**
     * Handle the WorkOrder "restored" event.
     *
     * @param  \App\Models\WorkOrder\WorkOrder  $workOrder
     * @return void
     */
    public function restored(WorkOrder $workOrder)
    {
        //
    }

    /**
     * Handle the WorkOrder "force deleted" event.
     *
     * @param  \App\Models\WorkOrder\WorkOrder  $workOrder
     * @return void
     */
    public function forceDeleted(WorkOrder $workOrder)
    {
        //
    }
}
