<?php

namespace App\Jobs\Remote;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Models\Host as HostModel;

class Host implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $this->host->load(['module']);
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

        $http = Http::remote($this->host->module->api_token, $this->host->module->url);
       
        switch ($this->type) {
            case 'patch':
                $response = $http->patch('hosts/' . $this->host->id, $this->host->toArray());
                break;
            case 'post':
                $response = $http->post('hosts', $this->host->toArray());

                break;

            case 'delete':
                $response = $http->delete('hosts/' . $this->host->id);

                // if success
                if ($response->successful()) {
                    $this->host->delete();
                }

                break;
        }

        if (!$response->successful()) {
            $this->host->status = 'error';
        }

        $this->host->save();
    }
}
