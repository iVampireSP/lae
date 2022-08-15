<?php

namespace App\Http\Controllers\Remote\WorkOrder;

use Illuminate\Http\Request;
use App\Models\WorkOrder\Reply;
use App\Http\Controllers\Controller;

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
        $request->validate([
            'content' => 'string|required|min:1|max:1000',
        ]);

        $reply = Reply::create([
            'content' => $request->content,
            'work_order_id' => $request->route('work_order'),
        ]);

        return $this->success($reply);
    }
}
