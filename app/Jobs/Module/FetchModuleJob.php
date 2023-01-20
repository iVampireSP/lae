<?php

namespace App\Jobs\Module;

use App\Jobs\Host\UpdateOrDeleteHostJob;
use App\Models\Module;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchModuleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected Module $module;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Module $module)
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
        $module = $this->module;

        $servers = Cache::get('module:' . $module->id . ':servers', []);

        try {
            $response = $module->http()->get('remote');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return;
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
