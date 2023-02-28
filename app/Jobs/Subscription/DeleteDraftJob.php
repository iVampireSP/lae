<?php

namespace App\Jobs\Subscription;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteDraftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 删除超过 1 天的草稿订阅。
     */
    public function handle(): void
    {
        Subscription::where('status', 'draft')
            ->where('created_at', '<', now()->subDay())
            ->delete();
    }
}
