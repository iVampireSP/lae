<?php

namespace App\Http\Controllers\User\WorkOrder;

use Illuminate\Http\Request;
use App\Models\WorkOrder\WorkOrder;
use App\Http\Controllers\Controller;

class WorkOrderController extends Controller
{
    //
    public function index(WorkOrder $workOrder)
    {

        $workOrder = $workOrder->thisUser()->with(['user', 'module', 'host'])->simplePaginate(10);

        return $this->success($workOrder);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'content' => 'required',
            'module_id' => 'nullable|sometimes|string|exists:modules,id',
            'host_id' => 'nullable|sometimes|exists:hosts,id',
        ]);


        $request_data = $request->toArray();

        // module_id 和 host_id 必须有个要填写
        if (isset($request_data['module_id']) && isset($request_data['host_id'])) {
            return $this->error('module_id 和 host_id 至少要填写一个');
        }

        $workOrder = WorkOrder::create([
            'title' => $request->title,
            'content' => $request->content,
            'module_id' => $request_data['module_id'] ?? null,
            'host_id' => $request_data['host_id'] ?? null,
            'status' => 'pending',
        ]);

        return $this->success($workOrder);
    }

    public function show(WorkOrder $workOrder)
    {
        if (auth()->id() !== $workOrder->user_id) {
            return $this->notFound('无法找到对应的工单。');
        }

        $workOrder->load(['module', 'host']);
        return $this->success($workOrder);
    }

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
