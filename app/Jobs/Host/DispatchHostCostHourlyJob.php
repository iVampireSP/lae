<?php

namespace App\Jobs\Host;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchHostCostHourlyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $minute;

    protected ?Host $host;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $minute, Host $host = null)
    {
        $this->minute = $minute;
        $this->host = $host;

        $this->onQueue('host-cost');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->host) {
            $host = new Host();

            if (app()->environment() != 'local') {
                $host = $host->where('minute_at', $this->minute);
            }

            $host->where('billing_cycle', 'hourly')->whereIn('status', ['running', 'stopped'])->with(['user', 'module'])->chunk(500, function ($hosts) {
                $hosts->each(function ($host) {
                    /* @var Host $host */

                    if ($host->module->isUp()) {
                        dispatch(new self($this->minute, $host));
                    }
                });
            });
        } else {
            if (! $this->host->isNextMonthCancel() && ! $this->host->isTrial()) {
                $this->host->cost($this->host->getPrice());
            }
        }
    }
}
