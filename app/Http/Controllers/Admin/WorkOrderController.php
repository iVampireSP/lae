<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CommonException;
use App\Http\Controllers\Controller;
use App\Models\WorkOrder\Reply;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param WorkOrder $workOrder
     *
     * @return View
     */
    public function index(WorkOrder $workOrder): View
    {
        $workOrders = $workOrder->with(['user', 'host'])->latest()->paginate(100);
        return view('admin.work-orders.index', compact('workOrders'));
    }

    /**
     * Display the specified resource.
     *
     * @param WorkOrder $workOrder
     *
     * @return View
     */
    public function show(WorkOrder $workOrder): View
    {
        //

        $workOrder->load(['user', 'module']);

        $replies = Reply::where('work_order_id', $workOrder->id)->latest()->paginate(100);

        return view('admin.work-orders.show', compact('workOrder', 'replies'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param WorkOrder $workOrder
     *
     * @return View
     */
    public function edit(WorkOrder $workOrder): View
    {
        //

        return view('admin.work-orders.edit', compact('workOrder'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request   $request
     * @param WorkOrder $workOrder
     *
     * @return RedirectResponse
     */
    public function update(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        //

        $request->validate([
            'status' => 'required|string|in:open,closed,read,user_read,replied,user_replied,on_hold,in_progress',
        ]);

        $workOrder->update([
            'status' => $request->status
        ]);

        return back()->with('success', '工单更新成功');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param WorkOrder $workOrder
     *
     * @return RedirectResponse
     */
    public function destroy(WorkOrder $workOrder): RedirectResponse
    {
        //
        try {
            $workOrder->safeDelete();
        } catch (CommonException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.work-orders.index')->with('success', '正在排队删除工单。');
    }

    public function reply(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        Reply::create([
            'content' => $request->input('content'),
            'work_order_id' => $workOrder->id
        ]);

        return back()->with('success', '回复成功，请等待同步。');
    }
}
