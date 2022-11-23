<?php

namespace App\Jobs\Module;

use App\Models\WorkOrder\Reply;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// use Illuminate\Contracts\Queue\ShouldBeUnique;

class PushWorkOrder implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

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

                if ($workOrder->host) {
                    if ($workOrder->host->status === 'pending') {
                        continue;
                    }
                }


                if ($workOrder->status === 'error') {
                    // 如果 created_at 超过 3 天 use Carbon
                    if (now()->diffInDays($workOrder->created_at) > 3) {
                        $workOrder->delete();
                        continue;
                    }
                }

                $http = Http::module($workOrder->module->api_token, $workOrder->module->url);
                $workOrder->status = 'open';

                $response = $http->post('work-orders', $workOrder->toArray());

                if (!$response->successful()) {
                    Log::error('推送工单失败', [
                        'work_order_id' => $workOrder->id,
                        'response' => $response->body(),
                    ]);
                    $workOrder->status = 'error';
                }

                $workOrder->save();

            }
        });

        Reply::where('is_pending', 1)->chunk(100, function ($replies) {
            foreach ($replies as $reply) {
                dispatch(new \App\Jobs\Module\WorkOrder\Reply($reply));
            }
        });

    }
}
