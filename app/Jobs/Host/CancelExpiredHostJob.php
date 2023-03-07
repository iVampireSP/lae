<?php

namespace App\Jobs\Host;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CancelExpiredHostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?Host $host;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Host $host = null)
    {
        $this->host = $host;

        $this->onQueue('host-cost');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = now();

        if (! $this->host) {
            $host = new Host();

            // 查找试用到期的主机
            $host->where('day_at', $now->day)
                ->where('hour_at', $now->hour)
                ->where('trial_ends_at', '<', $now)
                ->chunk(500, function ($hosts) {
                    $hosts->each(function ($host) {
                        /* @var Host $host */

                        if ($host->module->isUp()) {
                            dispatch(new self($host));
                        }
                    });
                });

            // 查找到期的主机
            $host->where('expired_at', '<', $now)
                ->chunk(500, function ($hosts) {
                    $hosts->each(function ($host) {
                        /* @var Host $host */

                        if ($host->module->isUp()) {
                            dispatch(new self($host));
                        }
                    });
                });
        } else {
            if ($this->host->isNextMonthCancel()) {
                $this->host->safeDelete();
            } else {
                $this->host->cost();
            }
        }
    }
}
