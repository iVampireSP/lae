<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Host;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function update(Request $request, Host $host): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:running,stopped',
        ]);

        $user = $request->user();

        if ($user->balances < 1) {
            return $this->error('余额不足，无法开启计费项目。');
        }

        if ($host->user_id == $user->id) {
            $host->update([
                'status' => $request->status,
            ]);

            return $this->updated($host);
        } else {
            return $this->error('无权操作');
        }
    }

    public function destroy(Host $host)
    {
        if ($host->user_id == auth()->id()) {
            if ($host->status == 'pending') {
                // 如果上次更新时间大于 5min
                if (time() - strtotime($host->updated_at) > 300) {
                    $host->delete();
                } else {
                    return $this->error('请等待 5 分钟后再试');
                }
            }

            dispatch(new \App\Jobs\Module\Host($host, 'delete'));
        } else {
            return $this->error('无权操作');
        }

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
