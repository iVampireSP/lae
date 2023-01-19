<?php

namespace App\Jobs\Support;

use App\Exceptions\EmqxSupportException;
use App\Support\EmqxSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EMQXKickClientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string|null $client_id, $username;
    protected bool $like_username;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($client_id, $username, $like_username = false)
    {
        $this->client_id = $client_id;
        $this->username = $username;
        $this->like_username = $like_username;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $emqx = new EmqxSupport();

        if ($this->client_id) {
            $emqx->api()->delete('/clients/' . $this->client_id);
        }

        if ($this->username) {
            $query = 'username';
            if ($this->like_username) {
                $query = 'like_username';
            }

            try {
                $clients = $emqx->clients([$query => $this->username]);
            } catch (EmqxSupportException $e) {
                Log::error('emqx connect failed.', [$e]);
                return;
            }

            if ($clients) {
                // 循环翻页
                for ($i = 1; $i <= $clients['meta']['count']; $i++) {
                    try {
                        $clients = $emqx->clients([$query => $this->username, 'page' => $i]);
                    } catch (EmqxSupportException $e) {
                        Log::error('emqx connect failed.', [$e]);
                        continue;
                    }

                    foreach ($clients['data'] as $client) {
                        dispatch(new self($client['clientid'], null));
                    }
                }
            }
        }
    }
}