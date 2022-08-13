<?php

namespace App\Http\Controllers\Admin\WorkOrder;

use App\Http\Controllers\Controller;
use App\Models\Workorder\Workorder;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Workorder $workorders)
    {
        //
        $workorders = $workorders->simplePaginate(10);
        return $this->success($workorders);
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Workorder\Workorder  $workorder
     * @return \Illuminate\Http\Response
     */
    public function show(Workorder $workorder)
    {
        //
        $workorder->load('replies');
        return $this->success($workorder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Workorder\Workorder  $workorder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Workorder $workorder)
    {
        // update
        $workorder->update($request->all());
        return $this->updated($workorder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Workorder\Workorder  $workorder
     * @return \Illuminate\Http\Response
     */
    public function destroy(Workorder $workorder)
    {
        //
        $workorder->delete();
        return $this->deleted($workorder);
    }
}
