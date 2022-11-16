<?php

namespace App\Http\Controllers\Modules\Host;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public $user_id, $host_id;


    /**
     * Display a listing of the resource.
     *
     * @return Response|null
     */
    public function index(Request $request)
    {
        //
        // $this->assignId($request);

        // return $this->getTasks();

        return null;
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
            'progress' => 'sometimes|integer|max:100',
            'status' => 'required|in:pending,processing,need_operation,done,success,failed,error,canceled',
        ]);

        $task = Task::create($request->all());

        return $this->success($task);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Task    $task
     *
     * @return JsonResponse
     */
    public function update(Request $request, Task $task)
    {
        //
        $this->validate($request, [
            'progress' => 'sometimes|integer|max:100',
            'status' => 'sometimes|in:pending,processing,need_operation,done,success,failed,error,canceled',
        ]);


        $task->update($request->all());


        return $this->updated($task);
    }
}
