<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Remote\WorkOrderRequest;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Http\JsonResponse;

class WorkOrderController extends Controller
{
    //
    public function index(WorkOrder $workOrder): JsonResponse
    {
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

    public function show(WorkOrderRequest $request, WorkOrder $workOrder): JsonResponse
    {
        return $this->success($workOrder);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(WorkOrderRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $this->validate($request, [
            'status' => 'nullable|sometimes|string|in:open,closed,on_hold,in_progress',
        ]);

        $workOrder->update($request->only('status'));
        return $this->success($workOrder);
    }

    // public function destroy() {}

}
