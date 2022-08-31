<?php

namespace App\Jobs\Remote;

use App\Models\Module\Module;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
        //
        Module::whereNotNull('url')->chunk(100, function ($modules)  {
            $servers = [];

            foreach ($modules as $module) {
                $http = Http::remote($module->api_token, $module->url);
                // dd($module->url);
                $response = $http->get('remote');

                if ($response->successful()) {
                    $json = $response->json();

                    if (isset($json['data']['servers'])) {
                        // 只保留 name, status
                        $servers = array_merge($servers, array_map(function ($server) use ($module) {
                            return [
                                'module_name' => $module->name,
                                'name' => $server['name'],
                                'status' => $server['status'],
                                'updated_at' => $server['updated_at'],
                            ];
                        }, $json['data']['servers']));
                    }
                    // $module->update([
                    //     'data' => $response->json()
                    // ]);
                }
            }

            // Cache servers
            Cache::put('servers', $servers, now()->addMinutes(10));
        });
    }
}
