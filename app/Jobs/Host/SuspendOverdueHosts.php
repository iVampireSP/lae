<?php

namespace App\Jobs\Host;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SuspendOverdueHosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?Host $host;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(?Host $host = null)
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
            (new Host)->where('next_due_at', '<', now())
                ->where('status', '!=', 'suspended')
                ->chunk(100, function ($hosts) {
                    foreach ($hosts as $host) {
                        dispatch(new self($host));
                    }
                });
        }

        $this->host?->suspend();
    }
}
