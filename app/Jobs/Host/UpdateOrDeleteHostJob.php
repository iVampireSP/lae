<?php

namespace App\Jobs\Host;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class UpdateOrDeleteHostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Host $host;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Host $host)
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
        $host = $this->host;

        $response = $host->module->baseRequest('get', 'hosts/' . $host->id);

        if ($response['status'] === 200) {
            $host->update(Arr::except($response['json'], ['id', 'user_id', 'module_id', 'created_at', 'updated_at']));
        } else if ($response['status'] === 404) {
            Log::warning($host->module->name . ' ' . $host->name . ' ' . $host->id . ' 不存在，删除。');
            dispatch(new HostJob($host, 'delete'));
        }
    }
}
