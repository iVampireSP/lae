<?php

namespace App\Http\Controllers\Remote\Host;

use App\Models\User\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    public $user_id, $host_id;


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        // $this->assignId($request);

        // return $this->getTasks();

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
     * @param  \Illuminate\Http\Request  $request
     * @param  Task $task
     * @return \Illuminate\Http\Response
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
