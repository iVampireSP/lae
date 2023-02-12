<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Host;
use Illuminate\Http\RedirectResponse;
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
        $hosts = Host::thisUser()->with(['user', 'module'])->paginate(20);

        return view('hosts.index', compact('hosts'));
    }

    public function renew(Host $host)
    {
        $price = $host->getRenewPrice();

        if ($price > auth()->user()->balance) {
            return back()->with('error', '余额不足，续费需要：' . $price . ' 元，您还需要充值：' . ($price - auth()->user()->balance) . ' 元');
        }

        if (!$host->isCycle()) {
            return back()->with('error', '该主机不是周期性付费，无法续费。');
        }

        if ($host->renew()) {
            return back()->with('success', '续费成功，新的到期时间为：' . $host->next_due_at . '。');
        }


        return back()->with('error', '续费失败，请检查是否有足够的余额。');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Host $host
     *
     * @return RedirectResponse
     */
    public function destroy(Host $host): RedirectResponse
    {
        $host->safeDelete();

        return redirect()->route('hosts . index')->with('success', '已添加到删除队列。');
    }
}
