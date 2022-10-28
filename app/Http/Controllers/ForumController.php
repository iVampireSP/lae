<?php

namespace App\Http\Controllers;

use Closure;
// use Exception;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ForumController extends Controller
{

    private $http, $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('forum.base_url');

        $this->http = Http::baseUrl($this->baseUrl . '/api')->throw();
    }

    public function get($url)
    {
        $resp = $this->http->get($url)->json()['data'];
        return $resp;
    }


    public function announcements()
    {
        $resp = $this->cache(function () {
            return $this->get('discussions?filter[tag]=announcements&page[offset]=0&sort=-createdAt');
        });

        return $this->resp($resp);
    }

    public function pinned()
    {
        $resp = $this->cache(function () {
            return $this->get('discussions?filter[tag]=pinned&page[offset]=0&sort=-createdAt');
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


    public function resp($data)
    {
        $data['base_url'] = $this->baseUrl;

        return $this->success($data);
    }
}
