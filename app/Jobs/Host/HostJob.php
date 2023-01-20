<?php

namespace App\Jobs\Host;

use App\Models\Host as HostModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// use Illuminate\Contracts\Queue\ShouldBeUnique;

class HostJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public HostModel $host;
    public string $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(HostModel $host, $type = 'post')
    {
        //
        $this->host = $host;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @noinspection PhpUndefinedVariableInspection
     */
    public function handle(): void
    {
        $host = $this->host;

        // 忽略 unavailable 状态的 host
        if ($host->status === 'unavailable') {
            return;
        }


        $host->load(['module']);

        switch ($this->type) {
            case 'patch':
                $response = $host->module->http()->patch('hosts/' . $host->id, $host->toArray());

                break;
            case 'post':
                $response = $host->module->http()->post('hosts', $host->toArray());

                break;
            case 'delete':
                $response = $host->module->baseRequest('delete', 'hosts/' . $host->id);

                // if successful
                if ($response['status'] === 404) {
                    $host->delete();
                }

                break;
        }

        if ($this->type !== 'delete') {
            if (!$response->successful()) {
                $host->status = 'error';
            }

            $host->save();
        }
    }
}
