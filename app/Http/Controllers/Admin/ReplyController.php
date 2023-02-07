<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder\Reply;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReplyController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param Request   $request
     * @param WorkOrder $work_order
     *
     * @return RedirectResponse
     */
    public function store(Request $request, WorkOrder $work_order): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        if ($work_order->isFailure()) {
            return back()->with('error', '工单还未就绪。');
        }

        (new Reply)->create([
            'content' => $request->input('content'),
            'work_order_id' => $work_order->id,
            'name' => auth('admin')->user()->name,
        ]);

        return back()->with('success', '回复成功，请等待同步。');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param WorkOrder $work_order
     * @param Reply     $reply
     *
     * @return View
     */
    public function edit(WorkOrder $work_order, Reply $reply): View
    {
        return view('admin.work-orders.reply_edit', compact('reply', 'work_order'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request   $request
     * @param WorkOrder $work_order
     * @param Reply     $reply
     *
     * @return RedirectResponse
     */
    public function update(Request $request, WorkOrder $work_order, Reply $reply): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $reply->update([
            'content' => $request->input('content'),
        ]);

        return redirect()->route('admin.work-orders.show', $work_order)->with('success', '修改成功。');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param WorkOrder $work_order
     * @param Reply     $reply
     *
     * @return RedirectResponse
     */
    public function destroy(WorkOrder $work_order, Reply $reply): RedirectResponse
    {
        $reply->safeDelete();

        return redirect()->route('admin.work-orders.show', $work_order)->with('success', '正在排队删除回复。');
    }
}
