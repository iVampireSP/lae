<?php

namespace App\Jobs\Host;

use App\Models\Host;
use App\Notifications\User\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRenewNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?Host $host;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(?Host $host)
    {
        $this->host = $host;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!$this->host) {
            // 获取 Host，距离今天刚好 7 天的 Host
            Host::where('next_due_at', '>', now()->addDays(7)->startOfDay())
                ->where('next_due_at', '<', now()->addDays(7)->endOfDay())
                ->chunk(100, function ($hosts) {
                    foreach ($hosts as $host) {
                        dispatch(new self($host));
                    }
                });
        }

        $this->host?->user->notify(new UserNotification("续费提醒", "您的 {$this->host->name} 将在 7 天后到期，请及时续费。", true));
    }
}
