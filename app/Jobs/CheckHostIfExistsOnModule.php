<?php

namespace App\Jobs;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;

class CheckHostIfExistsOnModule implements ShouldQueue
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
        // now 添加1.5小时

        //
        Host::with('module')->where('created_at', '<', now()->subHour())->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {
                $http = Http::remote($host->module->api_token, $host->module->url);
                $response = $http->get('hosts/' . $host->id);

                if ($response->status() === 404) {
                    Log::debug($host->module->name . ' ' . $host->name . ' ' . $host->id . ' 不存在，删除');
                    // dispatch(new \App\Jobs\Remote\Host($host, 'delete'));
                }
            }
        });
    }
}
