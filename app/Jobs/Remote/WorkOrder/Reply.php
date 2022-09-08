<?php

namespace App\Jobs\Remote\WorkOrder;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

// use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Models\WorkOrder\Reply as WorkOrderReply;
use Log;

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
            $this->reply->is_pending = false;
        } else {
            $this->reply->is_pending = true;
        }

        $this->reply->save();


    }
}
