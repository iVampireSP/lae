<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use function auth;

class WorkOrderController extends Controller
{
    public function index(WorkOrder $workOrder): JsonResponse
    {
        $workOrder = $workOrder->thisUser()->with(['user', 'module', 'host'])->simplePaginate(100);

        return $this->success($workOrder);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'content' => 'required',
            'module_id' => 'nullable|sometimes|string|exists:modules,id',
            'host_id' => 'nullable|sometimes|exists:hosts,id',
        ]);

        // module_id 和 host_id 必须有个要填写
        if (isset($request->module_id) && isset($request->host_id)) {
            return $this->error('module_id 和 host_id 至少要填写一个');
        }

        $workOrder = WorkOrder::create([
            'title' => $request->title,
            'content' => $request->input('content'),
            'module_id' => $request->module_id,
            'host_id' => $request->host_id,
            'status' => 'pending',
        ]);

        return $this->success($workOrder);
    }

    public function show(WorkOrder $workOrder): JsonResponse
    {
        if (auth()->id() !== $workOrder->user_id) {
            return $this->notFound('无法找到对应的工单。');
        }

        $workOrder->load(['module', 'host']);
        return $this->success($workOrder);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, WorkOrder $workOrder)
    {
        if (auth()->id() !== $workOrder->user_id) {
            return $this->notFound('无法找到对应的工单。');
        }

        $this->validate($request, [
            'status' => 'nullable|sometimes|string|in:closed',
        ]);

        $workOrder->update($request->only('status'));
        return $this->success($workOrder);
    }
}
