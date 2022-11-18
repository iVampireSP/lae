<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(WorkOrder $workOrder): View
    {
        $workOrders = $workOrder->with('user')->paginate(100);
        return view('admin.work-orders.index', compact('workOrders'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkOrder\WorkOrder  $workOrder
     * @return Response
     */
    public function show(WorkOrder $workOrder)
    {
        //

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\WorkOrder\WorkOrder $workOrder
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
     * @param \Illuminate\Http\Request        $request
     * @param \App\Models\WorkOrder\WorkOrder $workOrder
     *
     * @return RedirectResponse
     */
    public function update(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        //

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $workOrder->update($request->all());

        return back()->with('success', '工单更新成功');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkOrder\WorkOrder  $workOrder
     * @return Response
     */
    public function destroy(WorkOrder $workOrder)
    {
        //
    }
}
