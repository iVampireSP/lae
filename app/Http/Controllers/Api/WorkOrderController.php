<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WorkOrderController extends Controller
{
    public function index(WorkOrder $workOrder): JsonResponse
    {
        $workOrder = $workOrder->thisUser()->with(['user', 'module', 'host'])->latest()->simplePaginate(100);

        return $this->success($workOrder);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'content' => 'required|string|max:255',
            'module_id' => 'nullable|sometimes|string|exists:modules,id',
            'host_id' => 'nullable|sometimes|exists:hosts,id',
        ]);

        $workOrder = (new WorkOrder)->create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'module_id' => $request->input('module_id'),
            'host_id' => $request->input('host_id'),
            'status' => 'pending',
        ]);

        return $this->success($workOrder);
    }

    public function show(WorkOrder $workOrder): JsonResponse
    {
        $workOrder->load(['module', 'host']);

        $workOrder->markAsRead();

        return $this->success($workOrder);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $this->validate($request, [
            'status' => 'nullable|sometimes|string|in:closed',
        ]);

        // 访客不能关闭
        if ($request->input('status') === 'closed' && !auth('sanctum')->check()) {
            return $this->forbidden('访客不能修改工单状态。');
        }

        $workOrder->update($request->only('status'));

        return $this->success($workOrder);
    }
}
