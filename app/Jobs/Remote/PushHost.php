<?php

namespace App\Jobs\Remote;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class PushHost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        Host::whereIn('status', ['pending', 'error'])->with(['module', 'user'])->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {
                $http = Http::remote($host->module->api_token, $host->module->url);
                $host->status = 'running';
                
                $response = $http->post('hosts', $host->toArray());
                
                if (!$response->successful()) {
                    $host->status = 'error';
                }
                
                $host->save();
                
            }
        });
    }
}
