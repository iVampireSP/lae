<?php

namespace App\Http\Controllers\Admin\WorkOrder;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\WorkOrder\WorkOrder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(WorkOrder $work_order)
    {
        //
        $work_order = $work_order->simplePaginate(10);
        return $this->success($work_order);
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
        $this->validate($request, [
            'title' => 'required|max:255',
            'host_id' => 'required|integer|exists:hosts,id',
            'content' => 'required|max:255',
        ]);

        $data = [
            'user_id' => $request->route('user'),
            'title' => $request->title,
            'host_id' => $request->host_id,
            'content' => $request->content,
        ];

        $work_order = WorkOrder::create($data);


        return $this->created($work_order);
    }

    /**
     * Display the specified resource.
     *
     * @param User      $user
     * @param WorkOrder $work_order
     *
     * @return JsonResponse
     */
    public function show(User $user, Workorder $work_order)
    {
        //
        $work_order->load('replies');
        return $this->success($work_order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request   $request
     * @param User      $user
     * @param WorkOrder $work_order
     *
     * @return JsonResponse
     */
    public function update(Request $request, User $user, WorkOrder $work_order)
    {
        $data = Arr::only($request->all(), [
            'title',
            'content',
        ]);

        $work_order->update($data);
        return $this->updated($work_order);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User      $user
     * @param WorkOrder $work_order
     *
     * @return JsonResponse
     */
    public function destroy(User $user, WorkOrder $work_order)
    {
        //
        $work_order->delete();
        return $this->deleted($work_order);
    }
}
