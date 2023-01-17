<?php

namespace App\Jobs\WorkOrder;

use App\Models\WorkOrder\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
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
        (new WorkOrder)->whereIn('status', ['pending', 'error'])->with(['module', 'user', 'host', 'replies'])->chunk(100, function ($workOrders) {
            foreach ($workOrders as $workOrder) {

                if ($workOrder->host) {
                    if ($workOrder->host->status === 'pending') {
                        continue;
                    }
                }


                if ($workOrder->status === 'error') {
                    // 如果超过 3 次错误，使用 Redis
                    $count = Cache::get('work_order_error_count_' . $workOrder->id, 0);
                    if ($count > 3) {
                        $workOrder->delete();
                        continue;
                    } else {
                        Cache::increment('work_order_error_count_' . $workOrder->id);
                    }
                }

                $workOrder->status = 'open';

                $response = $workOrder->module->http()->post('work-orders', $workOrder->toArray());

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

        (new \App\Models\WorkOrder\Reply)->where('is_pending', 1)->chunk(100, function ($replies) {
            foreach ($replies as $reply) {
                dispatch(new Reply($reply));
            }
        });

    }
}
