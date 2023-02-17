<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\HostRequest;
use App\Models\Host;
use function auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use function now;

class HostController extends Controller
{
    public function index(): JsonResponse
    {
        $hosts = (new Host)->where('user_id', auth()->id())->with('module', function ($query) {
            $query->select(['id', 'name']);
        })->get();

        return $this->success($hosts);
    }

    public function update(HostRequest $request, Host $host): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:running,stopped,suspended',
        ]);

        $status = $host->changeStatus($request->input('status'));

        return $status ? $this->updated($host) : $this->failed('修改失败，请检查是否有足够的余额。');
    }

    public function destroy(HostRequest $request, Host $host): JsonResponse
    {
        unset($request);

        if ($host->isPending()) {
            // 如果上次更新时间大于 5min
            if ($host->updated_at->diffInMinutes(now()) > 5) {
                $host->delete();
            } else {
                return $this->error('请等待 5 分钟后再试');
            }
        }

        $host->safeDelete();

        return $this->deleted($host);
    }

    public function usages(): JsonResponse
    {
        $month = now()->month;

        $month_cache_key = 'user_'.auth()->id().'_month_'.$month.'_hosts_balances';
        $hosts_balances = Cache::get($month_cache_key, []);

        return $this->success([
            'balances' => $hosts_balances,
        ]);
    }
}
