<?php

namespace App\Http\Controllers\User\WorkOrder;

use Illuminate\Http\Request;
use App\Models\WorkOrder\WorkOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\WorkOrder\WorkOrderRequest;

class WorkOrderController extends Controller
{
    //
    public function index(Request $request, WorkOrder $workOrder) {

        $workOrder = $workOrder->thisUser()->with(['user', 'module', 'host'])->simplePaginate(10);

        return $this->success($workOrder);
    }

    public function store(Request $request) {
        $this->validate($request, [
            'title' => 'required|max:255',
            'content' => 'required',
            'module_id' => 'nullable|sometimes|string|exists:modules,id',
            'host_id' => 'nullable|sometimes|exists:hosts,id',
        ]);

        // module_id 和 host_id 必须有个要填写
        if ($request['module_id'] == null && $request['host_id'] = null) {
            return $this->error('module_id 和 host_id 至少要填写一个');
        }

        $workOrder = WorkOrder::create([
            'title' => $request->title,
            'content' => $request['content'],
            'module_id' => $request->module_id,
            'host_id' => $request->host_id,
            'status' => 'pending',
        ]);

        return $this->success($workOrder);

    }

    public function show(WorkOrderRequest $request, WorkOrder $workOrder) {
        $workOrder->load(['module', 'host']);
        return $this->success($workOrder);
    }

    public function update(WorkOrderRequest $request, WorkOrder $workOrder) {
        $this->validate($request, [
            'status' => 'nullable|sometimes|string|in:closed',
        ]);

        $workOrder->update($request->only('status'));
        return $this->success($workOrder);
    }
}
