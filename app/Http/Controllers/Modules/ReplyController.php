<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder\Reply;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        //
        $replies = Reply::workOrderId($request->route('work_order'))->simplePaginate(10);
        return $this->success($replies);
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     */
    public function store(Request $request, WorkOrder $work_order): JsonResponse
    {
        $this->validate($request, [
            'content' => 'required|string|max:255',
            'work_order_id' => 'required|integer|exists:work_orders,id',
        ]);

        if ($work_order->module_id !== auth('module')->id()) {
            return $this->error('您没有权限回复此工单。');
        }

        // 需要转换成数组
        $request_array = $request->all();

        $reply = Reply::create([
            'content' => $request_array['content'],
            'work_order_id' => $request_array['work_order_id'],
        ]);

        return $this->success($reply);
    }
}
