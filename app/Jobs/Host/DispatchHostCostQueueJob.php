<?php

namespace App\Jobs\Host;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchHostCostQueueJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $minute;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($minute)
    {
        //
        $this->minute = $minute;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $host = new Host();

        if (app()->environment() != 'local') {
            $host = $host->where('minute_at', $this->minute);
        }

        $host->whereIn('status', ['running', 'stopped'])->with('user')->chunk(500, function ($hosts) {
            foreach ($hosts as $host) {
                dispatch(new RealHostCostJob($host, $host->getPrice()));
            }
        });
    }
}
