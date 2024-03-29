<?php

namespace App\Jobs\Host;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// use Illuminate\Contracts\Queue\ShouldBeUnique;

class PushHostJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        (new Host)->whereIn('status', ['pending', 'error'])->with(['module', 'user'])->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {
                $host->status = 'running';

                $response = $host->module->http()->post('hosts', $host->toArray());

                if (! $response->successful()) {
                    $host->status = 'error';
                }

                // dd($response);
                $response_json = $response->json();

                // 检测是否有价格
                if (isset($response_json['price'])) {
                    $host->price = $response_json['price'];
                } else {
                    $host->status = 'error';
                }

                $host->save();
            }
        });
    }
}
