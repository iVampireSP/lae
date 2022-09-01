<?php

namespace App\Jobs\Remote;

use App\Models\Module\Module;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class FetchModule implements ShouldQueue
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
        // 获取运行完成的时间

        $last_run = Cache::get('servers_updated_at', false);
        if ($last_run !== false) {
            // 如果和上次运行时间间隔小于一分钟，则不运行
            if (now()->diffInMinutes($last_run) < 1) {
                return;
            }
        }

        //
        Module::whereNotNull('url')->chunk(100, function ($modules) {
            $servers = [];

            foreach ($modules as $module) {
                try {
                    $http = Http::remote($module->api_token, $module->url);
                    // dd($module->url);
                    $response = $http->get('remote');
                } catch (ConnectException $e) {
                    Log::error($e->getMessage());
                    continue;
                }


                if ($response->successful()) {
                    $json = $response->json();

                    if (isset($json['data']['servers'])) {
                        // 只保留 name, status
                        $servers = array_merge($servers, array_map(function ($server) use ($module) {
                            return [
                                'module_id' => $module->id,
                                'module_name' => $module->name,
                                'name' => $server['name'],
                                'status' => $server['status'],
                                'created_at' => $server['created_at'],
                                'updated_at' => $server['updated_at'],
                            ];
                        }, $json['data']['servers']));
                    }
                    // $module->update([
                    //     'data' => $response->json()
                    // ]);
                }
            }

            // if local
            if (config('app.env') === 'local') {
                Cache::forever('servers', $servers);
            } else {
                Cache::put('servers', $servers, now()->addMinutes(10));
            }

            // 缓存运行完成的时间
            Cache::put('servers_updated_at', now(), now()->addMinutes(10));
        });
    }
}
