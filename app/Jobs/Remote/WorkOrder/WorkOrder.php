<?php

namespace App\Jobs\Remote\WorkOrder;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Models\WorkOrder\WorkOrder as WorkOrderWorkOrder;

class WorkOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $workOrder, $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WorkOrderWorkOrder $workOrder,  $type = 'post')
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

        $http = Http::remote($this->workOrder->module->api_token, $this->workOrder->module->url);
        if ($this->type == 'put') {
            $response = $http->put('work-orders/' . $this->workOrder->id, $this->workOrder->toArray());
        } else {
            $response = $http->post('work-orders', $this->workOrder->toArray());
        }

        if (!$response->successful()) {
            $this->workOrder->status = 'error';
        }

        $this->workOrder->save();
    }
}
