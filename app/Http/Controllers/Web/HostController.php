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
     */
    public function index(): View
    {
        $hosts = (new Host)->thisUser()->with(['user', 'module'])->paginate(20);

        return view('hosts.index', compact('hosts'));
    }

    public function update(Request $request, Host $host): RedirectResponse
    {
        $request->validate([
            'status' => 'nullable|in:running,stopped,suspended',
            'cancel_at_period_end' => 'nullable|boolean',
        ]);

        if ($request->filled('status')) {
            $status = $host->changeStatus($request->input('status'));

            if (! $status) {
                return back()->with('error', '在修改主机状态时发生错误。');
            }
        }

        if ($request->filled('cancel_at_period_end')) {
            if ($host->isHourly()) {
                return back()->with('error', '按小时计费的主机无法进行此操作。');
            }

            $host->update([
                'cancel_at_period_end' => $request->boolean('cancel_at_period_end'),
            ]);
        }

        return back()->with('info', '更改已应用。');
    }

    /**
     * Remove the specified resource from storage.
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
