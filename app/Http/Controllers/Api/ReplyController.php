<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder\Reply;
use App\Models\WorkOrder\WorkOrder;
use function auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(WorkOrder $workOrder): JsonResponse
    {
        $replies = (new Reply)->workOrderId($workOrder->id)->with('module')->withUser()->simplePaginate(20);

        return $this->success($replies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $this->validate($request, [
            'content' => 'string|required|min:1|max:1000',
        ]);

        if ($workOrder->isFailure()) {
            return $this->error('工单状态异常，无法进行回复。请尝试重新建立工单。');
        }

        // 如果工单已经关闭，那么访客不能回复
        if ($workOrder->isClosed() && ! auth('sanctum')->check()) {
            return $this->error('工单已关闭，无法进行回复。');
        }

        $create = [
            'content' => $request->input('content'),
            'work_order_id' => $workOrder->id,
        ];

        if (auth('sanctum')->check()) {
            $create['user_id'] = auth('sanctum')->id();
            $create['name'] = auth('sanctum')->user()->name;
        } else {
            $this->validate($request, [
                'name' => 'string|required|min:1|max:255',
            ]);

            $create['name'] = $request->input('name');
        }

        $reply = (new Reply)->create($create);

        return $this->success($reply);
    }
}
