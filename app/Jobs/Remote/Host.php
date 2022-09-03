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

        // dd($this->host);

        // $host = HostModel::find($this->host);
        $host = $this->host;
        $host->load(['module']);

        $http = Http::remote($host->module->api_token, $host->module->url);

        switch ($this->type) {
            case 'patch':
                $response = $http->patch('hosts/' . $host->id, $host->toArray());
                break;
            case 'post':
                $response = $http->post('hosts', $host->toArray());

                break;

            case 'delete':
                $response = $http->delete('hosts/' . $host->id);

                if ($response->status() === 404) {
                    $host->delete();
                }

                return 0;
                // if response code is 404
                // if ($response->successful() || $response->failed()) {
                //     $host->delete();
                // }


                // if success
                // if ($response->successful()) {
                //     $host->delete();
                // }

                break;
        }

        if (!$response->successful()) {
            $host->status = 'error';
        }

        $host->save();
    }
}
