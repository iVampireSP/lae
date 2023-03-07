<?php

namespace App\Jobs\Host;

use App\Models\Host;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchHostCostQueueJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected Carbon $now;

    protected ?Host $host;

    protected string $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Carbon $now, Host $host = null, $type = 'hourly')
    {
        $this->now = $now;
        $this->host = $host;
        $this->type = $type;

        $this->onQueue('host-cost');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->host) {
            $host = new Host();

            if ($this->type == 'monthly') {
                // 月度计费，需要精确到天和小时
                $host = $host->where('day_at', $this->now->day);
                $host = $host->where('hour_at', $this->now->hour);
                $host = $host->where('cancel_at_period_end', false);
            } elseif (app()->environment() != 'local') {
                $host = $host->where('minute_at', $this->minute);
            }

            $host->where('billing_cycle', $this->type)->whereIn('status', ['running', 'stopped'])->with(['user', 'module'])->chunk(500, function ($hosts) {
                $hosts->each(function ($host) {
                    /* @var Host $host */

                    if ($host->module->isUp()) {
                        dispatch(new self($this->now, $host, $this->type));
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
