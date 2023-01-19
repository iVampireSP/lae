<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Http\Requests\Remote\WorkOrderRequest;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WorkOrderController extends Controller
{
    public function index(WorkOrder $workOrder): JsonResponse
    {
        $workOrder = $workOrder->thisModule()->simplePaginate(10);

        return $this->success($workOrder);
    }

    public function show(WorkOrder $workOrder): JsonResponse
    {
        return $this->success($workOrder);
    }

    /**
     * @throws ValidationException
     */
    public function update(WorkOrderRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $this->validate($request, [
            'status' => 'nullable|sometimes|string|in:open,closed,on_hold,in_progress,read',
        ]);

        $workOrder->update($request->only('status'));

        return $this->success($workOrder);
    }
}
