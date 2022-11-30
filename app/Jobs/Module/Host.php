<?php

namespace App\Jobs\Module;

use App\Models\Host as HostModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

// use Illuminate\Contracts\Queue\ShouldBeUnique;

class Host implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public HostModel $host;
    public string $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($host, $type = 'post')
    {
        //
        $this->host = $host;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        $host = $this->host;
        $host->load(['module']);

        switch ($this->type) {
            case 'patch':
                $response = $host->module->http()->patch('hosts/' . $host->id, $host->toArray());

                break;
            case 'post':
                $response = $host->module->http()->post('hosts', $host->toArray());

                break;
            case 'delete':
                $response = $host->module->http()->delete('hosts/' . $host->id);

                // if successful
                if ($response->successful() || $response->status() === 404) {
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
