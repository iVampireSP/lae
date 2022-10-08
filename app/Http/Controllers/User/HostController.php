<?php

namespace App\Http\Controllers\User;

use App\Models\Host;
use Illuminate\Http\Request;
// use App\Models\Module\Module;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class HostController extends Controller
{
    public function index()
    {
        //
        $hosts = (new Host())->getUserHosts(auth()->id());

        return $this->success($hosts);
    }

    public function update(Request $request, Host $host)
    {
        $user = $request->user();
        if ($host->user_id == $user->id) {

            if ($user->balance < 1) {
                return $this->error('余额不足');
            }

            $host->update([
                'status' => 'running'
            ]);

            return $this->updated($host);
        } else {
            return $this->error('无权操作');
        }

        return $this->deleted($host);
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

            dispatch(new \App\Jobs\Remote\Host($host, 'delete'));
        } else {
            return $this->error('无权操作');
        }

        return $this->deleted($host);
    }

    public function usages()
    {
        $month = now()->month;

        $month_cache_key = 'user_' . auth()->id() . '_month_' . $month . '_hosts_drops';
        $hosts_drops = Cache::get($month_cache_key, []);

        return $this->success($hosts_drops);
    }
}
