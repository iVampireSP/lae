<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\HostRequest;
use App\Models\Host;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use function auth;
use function dispatch;
use function now;

class HostController extends Controller
{
    public function index(): JsonResponse
    {
        $hosts = Host::where('user_id', auth()->id())->with('module', function ($query) {
            $query->select(['id', 'name']);
        })->get();

        return $this->success($hosts);
    }

    //
    public function update(HostRequest $request, Host $host): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:running,stopped',
        ]);

        $user = $request->user();

        if ($user->balance < 1) {
            return $this->error('余额不足，无法开启计费项目。');
        }

        $host->update([
            'status' => $request->status,
        ]);

        return $this->updated($host);
    }

    public function destroy(HostRequest $request, Host $host): JsonResponse
    {
        unset($request);

        if ($host->status == 'pending') {
            // 如果上次更新时间大于 5min
            if ($host->updated_at->diffInMinutes(now()) > 5) {
                $host->delete();
            } else {
                return $this->error('请等待 5 分钟后再试');
            }
        }

        // 如果时间大于 5 分钟，不满 1 小时
        if (now()->diffInMinutes($host->updated_at) > 5 && now()->diffInMinutes($host->updated_at) < 60) {
            $host->cost();
        }

        dispatch(new \App\Jobs\Module\Host($host, 'delete'));

        return $this->deleted($host);
    }

    public function usages(): JsonResponse
    {
        $month = now()->month;

        $month_cache_key = 'user_' . auth()->id() . '_month_' . $month . '_hosts_balances';
        $hosts_balances = Cache::get($month_cache_key, []);

        return $this->success([
            'balances' => $hosts_balances
        ]);
    }
}
