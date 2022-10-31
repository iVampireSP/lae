<?php

namespace App\Jobs\Remote\WorkOrder;

use Log;
use App\Events\UserEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;

// use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\WorkOrder\Reply as WorkOrderReply;

class Reply implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $reply;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WorkOrderReply $reply)
    {
        //
        $this->reply = $reply;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $this->reply->load(['workOrder', 'user']);
        $this->reply->workOrder->load(['module']);

        $http = Http::remote($this->reply->workOrder->module->api_token, $this->reply->workOrder->module->url);

        $reply = $this->reply->toArray();

        $response = $http->post('work-orders/' . $this->reply->workOrder->id . '/replies', $reply);

        if ($response->successful()) {
            $this->reply->update([
                'is_pending' => false
            ]);

            broadcast(new UserEvent($this->reply->workOrder->user_id, 'work-order.replied', $this->reply));

        } else {
            $this->reply->update([
                'is_pending' => true
            ]);
        }
    }
}
