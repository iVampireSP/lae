<?php

namespace App\Support;

use App\Exceptions\EmqxSupportException;
use App\Jobs\Support\EMQXKickClientJob;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class EmqxSupport
{
    private int $limit_per_page = 50;

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
            $this->api()->delete('/clients/'.$client_id);
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
    public function client(string $client_id)
    {
        $response = $this->api()->get('clients/'.$client_id);

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new EmqxSupportException('无法获取客户端信息。');
        }
    }

    /**
     * @throws EmqxSupportException
     */
    public function pagination($params = []): LengthAwarePaginator
    {
        $page = Request::input('page', 1);
        $params = array_merge([
            'page' => $page,
        ], $params);

        $clients = $this->clients($params);

        $data = $clients['data'];
        $total = $clients['meta']['count'];
        $limit = $clients['meta']['limit'];

        return new LengthAwarePaginator($data, $total, $limit, $page, [
            'path' => route('admin.devices.index'),
        ]);
    }

    /**
     * @throws EmqxSupportException
     */
    public function clients($params = [])
    {
        //    merge params
        $params = array_merge([
            'limit' => $this->limit_per_page,
            'isTrusted' => true,
        ], $params);

        try {
            $response = $this->api()->get('clients', $params);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ConnectionException $e) {
            Log::error('emqx connect failed.', [$e]);
            throw new EmqxSupportException('EMQX API 无法连接。'.$e->getMessage());
        }

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new EmqxSupportException('无法获取客户端列表。');
        }
    }
}
