<?php

namespace App\Jobs\WorkOrder;

use App\Models\WorkOrder\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

// use Illuminate\Contracts\Queue\ShouldBeUnique;

class PushWorkOrderJob implements ShouldQueue
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
    public function handle(): void
    {
        //
        (new WorkOrder)->whereIn('status', ['pending'])->with(['module', 'user', 'host', 'replies'])->chunk(100, function ($workOrders) {
            foreach ($workOrders as $workOrder) {

                if ($workOrder->host) {
                    if ($workOrder->host->status === 'pending') {
                        continue;
                    }
                }


                if ($workOrder->status === 'error') {
                    continue;
                }

                $workOrder->status = 'open';


                $success = false;

                try {
                    $response = $workOrder->module->http()->retry(3, 100)->post('work-orders', $workOrder->toArray());

                    if (!$response->successful()) {
                        Log::warning('推送工单失败', [
                            'work_order_id' => $workOrder->id,
                            'response' => $response->body(),
                        ]);
                        $workOrder->status = 'error';
                    }
                } catch (RequestException $e) {
                    Log::warning($e->getMessage());
                    $workOrder->status = 'error';
                }


                $workOrder->save();

            }
        });

        (new \App\Models\WorkOrder\Reply)->where('is_pending', 1)->chunk(100, function ($replies) {
            foreach ($replies as $reply) {
                dispatch(new Reply($reply));
            }
        });

    }
}
