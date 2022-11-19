<?php

namespace App\Jobs\Module\WorkOrder;

use App\Events\UserEvent;
use App\Models\WorkOrder\WorkOrder as WorkOrderWorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

// use Illuminate\Contracts\Queue\ShouldBeUnique;

class WorkOrder implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $workOrder, $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WorkOrderWorkOrder $workOrder, $type = 'post')
    {
        //
        $this->workOrder = $workOrder;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->workOrder->load(['module']);

        $http = Http::module($this->workOrder->module->api_token, $this->workOrder->module->url);
        if ($this->type == 'put') {
            $response = $http->put('work-orders/' . $this->workOrder->id, $this->workOrder->toArray());
        } else if ($this->type == 'delete') {
            $response = $http->delete('work-orders/' . $this->workOrder->id);

            if ($response->successful()) {
                $this->workOrder->delete();
            }
        } else {
            $response = $http->post('work-orders', $this->workOrder->toArray());
        }

        if (!$response->successful()) {
            $this->workOrder->update([
                'status' => 'error'
            ]);
        } else {
            if ($this->type == 'delete') {
                broadcast(new UserEvent($this->workOrder->user_id, 'work-order.deleted', $this->workOrder));
            } else {
                broadcast(new UserEvent($this->workOrder->user_id, 'work-order.updated', $this->workOrder));
            }
        }

    }
}
