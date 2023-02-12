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
        if ($host->renew()) {
            return back()->with('success', '续费成功，新的到期时间为：'.$host->next_due_at);
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
        $host->safeDelete();

        return back()->with('success', '已添加到销毁队列。');
    }
}
