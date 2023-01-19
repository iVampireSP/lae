<?php

namespace App\Jobs\WorkOrder;

use App\Models\WorkOrder\WorkOrder as WorkOrderModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// use Illuminate\Contracts\Queue\ShouldBeUnique;

class WorkOrder implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected WorkOrderModel $workOrder;
    protected string $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WorkOrderModel $workOrder, $type = 'post')
    {
        $this->workOrder = $workOrder->load(['module']);
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->workOrder->isPlatform()) {
            return;
        }


        if ($this->type == 'post') {
            $response = $this->workOrder->module->http()->post('work-orders', $this->workOrder->toArray());
        } else if ($this->type == 'put') {
            $response = $this->workOrder->module->http()->put('work-orders/' . $this->workOrder->id, $this->workOrder->toArray());
        } else {
            $response = $this->workOrder->module->http()->delete('work-orders/' . $this->workOrder->id);

            if ($response->successful()) {
                $this->workOrder->delete();
            }
        }

        if (!$response->successful()) {
            $this->workOrder->update([
                'status' => 'error'
            ]);
        }
    }
}
