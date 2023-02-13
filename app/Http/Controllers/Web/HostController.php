<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Host;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $hosts = (new Host)->thisUser()->with(['user', 'module'])->paginate(20);
        $times = config('settings.billing.cycle_delete_times_every_month') - Cache::get('host_delete_times:'.auth('web')->id(), 0);

        return view('hosts.index', compact('hosts', 'times'));
    }

    public function update(Request $request, Host $host): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:running,stopped,suspended',
        ]);

        $status = $host->changeStatus($request->input('status'));

        return $status ? back()->with('success', '修改成功。') : back()->with('error', '修改失败。');
    }

    public function renew(Host $host): RedirectResponse
    {
        $price = $host->getRenewPrice();

        if ($price > auth()->user()->balance) {
            return back()->with('error', '余额不足，续费需要：'.$price.' 元，您还需要充值：'.($price - auth()->user()->balance).' 元');
        }

        if (! $host->isCycle()) {
            return back()->with('error', '该主机不是周期性付费，无法续费。');
        }

        if ($host->renew()) {
            return back()->with('success', '续费成功，新的到期时间为：'.$host->next_due_at.'。');
        }

        return back()->with('error', '续费失败，请检查是否有足够的余额。');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Host  $host
     * @return RedirectResponse
     */
    public function destroy(Host $host): RedirectResponse
    {
        if ($host->isUnavailable()) {
            return back()->with('error', '为了安全起见，此主机只能由我们自动删除。');
        }

        $status = $host->safeDelete();

        if ($status) {
            return redirect()->route('hosts.index')->with('success', '已添加到删除队列。');
        } else {
            return back()->with('error', '删除失败。');
        }
    }
}
