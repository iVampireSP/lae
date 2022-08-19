<?php

namespace App\Jobs\Remote\WorkOrder;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Models\WorkOrder\Reply as WorkOrderReply;

class Reply implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $this->reply->load(['workOrder']);
        $this->reply->workOrder->load(['module']);
        // $this->reply->user = $this->reply->workOrder->user;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $http = Http::remote($this->reply->workOrder->module->api_token, $this->reply->workOrder->module->url);

        $response = $http->post('work-orders/' . $this->reply->workOrder->id . '/replies', $this->reply->toArray());

        if ($response->successful()) {
            $this->reply->is_pending = false;
        } else {
            $this->reply->is_pending = true;
        }

        $this->reply->save();


    }
}
