<?php

namespace App\Support;

use App\Exceptions\EmqxSupportException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class EmqxSupport
{
    private function api(): PendingRequest
    {
        return Http::baseUrl(config('emqx.api_url'))->withBasicAuth(config('emqx.api_key'), config('emqx.api_secret'));
    }

    /**
     * @throws EmqxSupportException
     */
    public function clients($params = [])
    {
        //    merge params
        $params = array_merge([
            'limit' => 100,
            'isTrusted' => true,
        ], $params);

        $response = $this->api()->get('clients', $params);

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new EmqxSupportException('无法获取客户端列表。');
        }
    }

    public function kickClient($client_id = null, $username = null): void
    {
        // 如果都为空，直接返回
        if (empty($client_id) && empty($username)) {
            return;
        }

        if ($client_id) {
            $this->api()->delete('/clients/' . $client_id);
        }

        if ($username) {
            $clients = $this->clients(['username' => $username]);

            if ($clients) {
                // 循环翻页
                for ($i = 1; $i <= $clients['meta']['count']; $i++) {
                    $clients = $this->clients(['username' => $username, 'page' => $i]);

                    foreach ($clients['data'] as $client) {
                        $this->api()->delete('/clients/' . $client['clientid']);
                    }
                }
            }
        }
    }
}