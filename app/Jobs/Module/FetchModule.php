<?php

namespace App\Jobs\Module;

use App\Events\ServerEvent;
use App\Models\Module;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchModule implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

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

        // $last_run = Cache::get('servers_updated_at', false);
        // if ($last_run !== false) {
        //     // 如果和上次运行时间间隔小于一分钟，则不运行
        //     if (now()->diffInMinutes($last_run) < 1) {
        //         return;
        //     }
        // }

        //
        Module::where('status', '!=', 'maintenance')->whereNotNull('url')->chunk(100, function ($modules) {
            $servers = [];

            foreach ($modules as $module) {
                try {
                    $http = Http::module($module->api_token, $module->url);
                    // dd($module->url);
                    $response = $http->get('remote');
                } catch (ConnectException $e) {
                    Log::error($e->getMessage());
                    continue;
                }

                if ($response->successful()) {

                    // 如果模块状态为 down，则更新为 up
                    if ($module->status === 'down') {
                        $module->status = 'up';
                    }

                    $json = $response->json();

                    if (isset($json['data']['servers']) && is_array($json['data']['servers'])) {
                        // 只保留 name, status
                        $servers = array_merge($servers, array_map(function ($server) use ($module) {
                            return [
                                'name' => $server['name'],
                                'status' => $server['status'],
                                'created_at' => $server['created_at'] ?? now(),
                                'updated_at' => $server['updated_at'] ?? now(),
                                'module' => [
                                    'id' => $module->id,
                                    'name' => $module->name,
                                ]
                            ];
                        }, $json['data']['servers']));

                        broadcast(new ServerEvent($servers));
                    }

                } else {

                    // if module return maintenance, then set module status to maintenance
                    if ($response->status() == 503) {
                        $module->status = 'maintenance';
                    } else {
                        $module->status = 'down';
                    }
                }

                $module->save();
            }

            // if local
            if (config('app.env') === 'local') {
                Cache::forever('servers', $servers);
            } else {
                Cache::put('servers', $servers, now()->addMinutes(10));
            }

            // 缓存运行完成的时间
            // Cache::put('servers_updated_at', now(), now()->addMinutes(10));
        });
    }
}
