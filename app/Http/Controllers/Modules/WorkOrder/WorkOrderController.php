<?php

namespace App\Http\Controllers\Modules\WorkOrder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Remote\WorkOrderRequest;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    //
    public function index(Request $request, WorkOrder $workOrder) {
        // $work_orders = new WorkOrder();
        // // if route has user
        // if ($request->route('user')) {
        //     $work_orders = $work_orders->where('user_id', $request->route('user'));
        // }

        // $work_orders = $work_orders->simplePaginate(10);

        $workOrder = $workOrder->thisModule()->simplePaginate(10);

        return $this->success($workOrder);
    }

    // public function store(Request $request) {

    // }

    public function show(WorkOrderRequest $request, WorkOrder $workOrder) {
        return $this->success($workOrder);
    }

    public function update(WorkOrderRequest $request, WorkOrder $workOrder)
    {
        $this->validate($request, [
            'status' => 'nullable|sometimes|string|in:open,closed,on_hold,in_progress',
        ]);

        $workOrder->update($request->only('status'));
        return $this->success($workOrder);
    }

    // public function destroy() {}

}
