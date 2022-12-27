<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder\Reply;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function auth;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
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
     * @param Request   $request
     * @param WorkOrder $workOrder
     *
     * @return JsonResponse|Response
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
            'content' => $request->input('content'),
            'work_order_id' => $workOrder->id,
        ]);


        return $this->success($reply);
    }
}
