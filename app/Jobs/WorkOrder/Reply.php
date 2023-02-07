<?php

namespace App\Jobs\WorkOrder;

use App\Models\WorkOrder\Reply as WorkOrderReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Reply implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected WorkOrderReply $reply;

    protected string $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WorkOrderReply $reply, $type = 'post')
    {
        $this->reply = $reply->load(['workOrder', 'user']);
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->reply->workOrder->isPlatform()) {
            if ($this->reply->is_pending) {
                $this->reply->update(['is_pending' => false]);
            }

            return;
        }

        $this->reply->workOrder->load(['module']);

        $reply = $this->reply->toArray();

        if ($this->type == 'post') {
            $response = $this->reply->workOrder->module->http()->post('work-orders/'.$this->reply->workOrder->id.'/replies', $reply);

            if ($response->successful()) {
                $this->reply->update([
                    'is_pending' => false,
                ]);
            } else {
                $this->reply->update([
                    'is_pending' => true,
                ]);
            }
        } elseif ($this->type == 'patch') {
            // 不更新 is_pending
            if ($this->reply->workOrder->isPlatform()) {
                $this->reply->update([
                    'is_pending' => false,
                ]);

                return;
            }

            $this->reply->workOrder->module->http()->patch('work-orders/'.$this->reply->workOrder->id.'/replies/'.$this->reply->id, $reply);
        } elseif ($this->type == 'delete') {
            $response = $this->reply->workOrder->module->http()->delete('work-orders/'.$this->reply->workOrder->id.'/replies/'.$this->reply->id);

            if ($response->successful()) {
                $this->reply->delete();
            }
        }
    }
}
