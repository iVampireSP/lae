<?php

namespace App\Support;

use App\Exceptions\EmqxSupportException;
use App\Jobs\Support\EMQXKickClientJob;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmqxSupport
{
    /**
     * 移除客户端
     */
    public function kickClient($client_id = null, $username = null, bool $like_username = false): void
    {
        // 如果都为空，直接返回
        if (empty($client_id) && empty($username)) {
            return;
        }

        if ($client_id) {
            $this->api()->delete('/clients/' . $client_id);
        }

        if ($username) {
            dispatch(new EMQXKickClientJob(null, $username, $like_username));
        }
    }

    public function api(): PendingRequest
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

        try {
            $response = $this->api()->get('clients', $params);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ConnectionException $e) {
            Log::error('emqx connect failed.', [$e]);
            throw new EmqxSupportException('EMQX API 无法连接。' . $e->getMessage());
        }

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new EmqxSupportException('无法获取客户端列表。');
        }
    }
}
