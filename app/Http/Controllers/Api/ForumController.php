<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use function config;

class ForumController extends Controller
{

    private mixed $baseUrl;
    private PendingRequest $http;

    public function __construct()
    {
        $this->baseUrl = config('forum.base_url');
        $this->http = Http::baseUrl($this->baseUrl . '/api')->throw();
    }

    public function announcements(): JsonResponse
    {
        $resp = $this->cache(function () {
            return $this->get('discussions?filter[tag]=announcements&page[offset]=0&sort=-createdAt');
        });

        return $this->resp($resp);
    }

    public function cache(Closure $callback)
    {
        // 获取调用方法名
        $method = debug_backtrace()[1]['function'];

        return Cache::remember('forum.func.' . $method, 60, function () use ($callback) {
            return $callback();
        });
    }

    public function get($url)
    {
        return $this->http->get($url)->json()['data'];
    }

    public function resp($data): JsonResponse
    {
        $data['base_url'] = $this->baseUrl;

        return $this->success($data);
    }

    public function pinned(): JsonResponse
    {
        $resp = $this->cache(function () {
            return $this->get('discussions?filter[tag]=pinned&page[offset]=0&sort=-createdAt');
        });

        return $this->resp($resp);
    }
}
