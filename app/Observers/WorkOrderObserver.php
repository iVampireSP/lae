<?php

namespace App\Observers;

use App\Exceptions\CommonException;
use App\Jobs\WorkOrder\WorkOrder as WorkOrderJob;
use App\Models\WorkOrder\WorkOrder;
use App\Notifications\WorkOrder\WorkOrder as WorkOrderNotification;
use Illuminate\Support\Str;

class WorkOrderObserver
{
    /**
     * @throws CommonException
     */
    public function creating(WorkOrder $workOrder): void
    {
        $workOrder->uuid = Str::uuid()->toString();

        if ($workOrder->host_id) {
            $workOrder->load(['host']);
            $workOrder->module_id = $workOrder->host->module_id;
        }

        if (auth('sanctum')->check()) {
            $workOrder->user_id = auth()->id();

            if ($workOrder->host_id) {
                if (! $workOrder->user_id == $workOrder->host->user_id) {
                    throw new CommonException('user_id not match host user_id');
                }
            }
        } else {
            if (! $workOrder->user_id) {
                throw new CommonException('user_id is required');
            }
        }

        if ($workOrder->host_id) {
            $workOrder->host->load('module');
            $module = $workOrder->host->module;

            if ($module === null) {
                $workOrder->status = 'open';
            } else {
                $workOrder->status = 'pending';
            }
        }

        $workOrder->notify = true;

        $workOrder->ip = request()->ip();
    }

    public function updated(WorkOrder $workOrder): void
    {
        dispatch(new WorkOrderJob($workOrder, 'put'));

        if ($workOrder->notify && $workOrder->isDirty('status')) {
            $workOrder->notify(new WorkOrderNotification($workOrder));
        }
    }
}
