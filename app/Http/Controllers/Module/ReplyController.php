<?php

namespace App\Http\Controllers\Module;

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
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $replies = (new Reply)->workOrderId($request->route('work_order'))->simplePaginate(10);

        return $this->success($replies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, WorkOrder $work_order): JsonResponse
    {
        $this->validate($request, [
            'content' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ]);

        $reply = (new Reply)->create([
            'content' => $request->input('content'),
            'work_order_id' => $work_order->id,
            'module_id' => $work_order->module_id,
            'name' => $request->input('name'),
        ]);

        return $this->success($reply);
    }
}
