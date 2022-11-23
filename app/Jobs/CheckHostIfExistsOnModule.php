<?php

namespace App\Jobs;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
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
        // 删除所有模块中不存在的主机
        Host::with('module')->where('created_at', '<', now()->subHour())->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {

                // 忽略维护中的模块
                if ($host->module->status !== 'up') {
                    continue;
                }

                $http = Http::module($host->module->api_token, $host->module->url);
                $response = $http->get('hosts/' . $host->id);

                if ($response->status() === 404) {
                    Log::warning($host->module->name . ' ' . $host->name . ' ' . $host->id . ' 不存在，删除。');
                    dispatch(new \App\Jobs\Module\Host($host, 'delete'));
                }
            }
        });
    }
}
