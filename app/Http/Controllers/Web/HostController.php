<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Host;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('hosts.index', compact('hosts'));
    }

    public function update(Request $request, Host $host): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:running,stopped,suspended',
        ]);

        $status = $host->changeStatus($request->input('status'));

        return $status ? back()->with('success', '修改成功。') : back()->with('error', '修改失败。');
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
