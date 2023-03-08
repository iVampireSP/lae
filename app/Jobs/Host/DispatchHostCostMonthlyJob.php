<?php

namespace App\Jobs\Host;

use App\Models\Host;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchHostCostMonthlyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Carbon $now;

    protected int $day;

    protected int $hour;

    protected ?Host $host;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $day, int $hour, Host $host = null)
    {
        $this->day = $day;
        $this->hour = $hour;

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

            // 月度计费，需要精确到天和小时
            $host = $host->where('day_at', $this->day);
            $host = $host->where('hour_at', $this->hour);
            $host = $host->where('cancel_at_period_end', false);

            $host->where('billing_cycle', 'monthly')->whereIn('status', ['running', 'stopped'])->with(['user', 'module'])->chunk(500, function ($hosts) {
                $hosts->each(function ($host) {
                    /* @var Host $host */

                    if ($host->module->isUp()) {
                        dispatch(new self($this->day, $this->hour, $host));
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
