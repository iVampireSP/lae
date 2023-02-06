<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Closure;
use function config;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ForumController extends Controller
{
    private mixed $baseUrl;

    private PendingRequest $http;

    public function __construct()
    {
        $this->baseUrl = config('settings.forum.base_url');
        $this->http = Http::baseUrl($this->baseUrl.'/api')->throw();
    }

    public function cache($tag, Closure $callback)
    {
        return Cache::remember('forum.tag:'.$tag, 60, function () use ($callback) {
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

    public function tag($tag): JsonResponse
    {
        $resp = $this->cache($tag, function () use ($tag) {
            return $this->get('discussions?filter[tag]='.$tag.'&page[offset]=0&sort=-createdAt');
        });

        return $this->resp($resp);
    }
}
