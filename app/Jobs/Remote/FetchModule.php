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
        Module::chunk(100, function ($modules) {
            foreach ($modules as $module) {
                $http = Http::remote($module->api_token, $module->url);
                // dd($module->url);
                $response = $http->get('remote');

                if ($response->successful()) {
                    Cache::set('module_' . $module->id, $response->status());
                    // $module->update([
                    //     'data' => $response->json()
                    // ]);
                }

            }
        });
    }
}
