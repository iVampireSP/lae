<?php

namespace App\Jobs\Module;

use App\Models\Module;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DispatchFetchModuleJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected ?Module $module;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Module $module = null)
    {
        $this->module = $module;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!$this->module) {
            (new Module)->whereNotNull('url')->chunk(100, function ($modules) {
                foreach ($modules as $module) {
                    dispatch(new self($module));

                }
            });
        }

        if ($this->module) {
            $module = $this->module;

            $servers = [];

            try {
                $response = $module->http()->get('remote');
            } catch (Exception $e) {
                Log::debug('无法连接到模块 - down: ' . $e->getMessage());

                // 如果模块状态不为 down，则更新为 down
                if ($module->status !== 'down') {
                    $module->status = 'down';
                    $module->save();
                }

                return;
            }

            if ($response->successful()) {
                // 如果模块状态不为 up，则更新为 up
                if ($module->status !== 'up') {
                    $module->status = 'up';
                    Log::debug('模块状态更新为 up: ' . $module->name);
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
                            ],
                        ];
                    }, $json['servers']));

                    // broadcast(new Servers($servers));
                }
            } else {
                // if module return maintenance, then set module status to maintenance
                $status = $response->status();
                if ($status == 503 || $status == 429 || $status == 502) {
                    $module->status = 'maintenance';
                } else {
                    $module->status = 'down';
                }

                Log::debug('模块状态更新为 ' . $module->status . ': ' . $module->name);
            }

            $module->save();

            // if local
            if (config('app.env') === 'local') {
                Cache::forever('module:' . $module->id . ':servers', $servers);
            } else {
                Cache::put('module:' . $module->id . ':servers', $servers, now()->addMinutes(10));
            }
        }
    }
}
