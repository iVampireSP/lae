<?php

namespace App\Jobs\Remote;

use App\Models\WorkOrder\Reply;
use Illuminate\Bus\Queueable;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class PushWorkOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 
        WorkOrder::whereIn('status', ['pending', 'error'])->with(['module', 'user', 'host', 'replies'])->chunk(100, function ($workOrders) {
            foreach ($workOrders as $workOrder) {

                if ($workOrder->host->status === 'pending') {
                    continue;
                }

                $http = Http::remote($workOrder->module->api_token, $workOrder->module->url);
                $workOrder->status = 'open';

                $response = $http->post('work-orders', $workOrder->toArray());

                if (!$response->successful()) {
                    $workOrder->status = 'error';
                }

                $workOrder->save();
                
            }
        });

        Reply::where('is_pending', 1)->chunk(100, function ($replies) {
            foreach ($replies as $reply) {
                dispatch(new \App\Jobs\Remote\WorkOrder\Reply($reply));
            }
        });

    }
}
