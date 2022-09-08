<?php

namespace App\Http\Controllers\User\WorkOrder;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\WorkOrder\WorkOrderRequest;
use App\Models\WorkOrder\Reply;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(WorkOrderRequest $request)
    {
        //

        $replies = Reply::workOrderId($request->route('work_order'))->simplePaginate(10);

        return $this->success($replies);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WorkOrderRequest $request)
    {
        // add reply
        $this->validate($request, [
            'content' => 'string|required|min:1|max:1000',
        ]);






        $reply = Reply::create([
            'content' => $request->toArray()['content'],
            'work_order_id' => $request->route('workOrder')->id,
        ]);


        return $this->success($reply);
    }
}
