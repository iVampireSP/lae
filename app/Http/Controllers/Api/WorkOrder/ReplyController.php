<?php

namespace App\Http\Controllers\Api\WorkOrder;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder\Reply;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Http\Request;
use function auth;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(WorkOrder $workOrder)
    {
        if (auth()->id() !== $workOrder->user_id) {
            return $this->notFound('无法找到对应的工单。');
        }

        $replies = Reply::workOrderId($workOrder->id)->simplePaginate(100);

        return $this->success($replies);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request, WorkOrder $workOrder)
    {
        if (auth()->id() !== $workOrder->user_id) {
            return $this->notFound('无法找到对应的工单。');
        }

        // add reply
        $this->validate($request, [
            'content' => 'string|required|min:1|max:1000',
        ]);


        $reply = Reply::create([
            'content' => $request->toArray()['content'],
            'work_order_id' => $workOrder->id,
        ]);


        return $this->success($reply);
    }
}
