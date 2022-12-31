<?php

namespace App\Jobs\Module;

use App\Events\ServerEvent;
use App\Models\Module;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchModuleJob implements ShouldQueue
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
    public function handle(): void
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
        Module::whereNotNull('url')->chunk(100, function ($modules) {
            $servers = [];

            foreach ($modules as $module) {
                try {
                    $response = $module->http()->get('remote');
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                    continue;
                }

                if ($response->successful()) {

                    // 如果模块状态不为 up，则更新为 up
                    if ($module->status !== 'up') {
                        $module->status = 'up';
                    }

                    $json = $response->json();

                    if (isset($json['servers']) && is_array($json['servers'])) {
                        // 只保留 name, status, meta
                        $servers = array_merge($servers, array_map(function ($server) use ($module) {
                            return [
                                'name' => $server['name'],
                                'status' => $server['status'],
                                'meta' => $server['meta'] ?? [],
                                'created_at' => $server['created_at'] ?? now(),
                                'updated_at' => $server['updated_at'] ?? now(),
                                'module' => [
                                    'id' => $module->id,
                                    'name' => $module->name,
                                ]
                            ];
                        }, $json['servers']));

                        // broadcast(new ServerEvent($servers));
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
