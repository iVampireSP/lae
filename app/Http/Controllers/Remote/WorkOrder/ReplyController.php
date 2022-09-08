<?php

namespace App\Http\Controllers\Remote\WorkOrder;

use Illuminate\Http\Request;
use App\Models\WorkOrder\Reply;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
    public function store(Request $request)
    {
        //

        $request_array = $request->all();

        $reply = Reply::create([
            'content' => $request_array['content'],
            'work_order_id' => $request_array['work_order_id'],
        ]);

        return $this->success($reply);
    }
}
