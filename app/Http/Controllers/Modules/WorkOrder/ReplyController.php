<?php

namespace App\Http\Controllers\Modules\WorkOrder;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
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
     * @param Request $request
     *
     * @return JsonResponse
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
