<?php

namespace App\Http\Controllers\Remote;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;

class TaskController extends Controller
{
    public $user_id, $host_id;


    public function __construct(Request $request)
    {
        $request->validate([
            'host_id' => 'sometimes|integer|exists:hosts,id',
            'user_id' => 'integer|exists:users,id',
        ]);

        $this->user_id = $request->user_id;
        $this->host_id = $request->host_id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'title' => 'required|max:255',
            'progress' => 'sometimes|integer|max:100',
            'status' => 'required|in:pending,processing,need_operation,done,success,failed,error,canceled',
        ]);

        return $this->created($this->pushTask([
            'title' => $request->title,
            'progress' => $request->progress,
            'status' => $request->status,
        ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id' => 'max:40|required',
        ]);

        $tasks = $this->getTasks();

        // check if task exists
        if (!isset($tasks[$id])) {
            // create task
            $task = [
                'title' => $request->title,
                'progress' => 0,
                'status' => $request->status,
            ];
            $this->pushTask($task, $request->id);
        } else {
            $task = $tasks[$id];
        }

        // patch task
        $task = array_merge($task, $request->only(['title', 'progress', 'status']));

        // update task
        $this->pushTask($task, $request->id);
    }

    public function getTasks()
    {
        $cache_key = 'user_tasks_' . $this->user_id;
        return Cache::get($cache_key, []);
    }

    public function pushTask($task, $id = null)
    {
        $cache_key = 'user_tasks_' . $this->user_id;
        $data = [
            'user_id' => $this->user_id,
            'done_at' => null,
            'host_id' => $this->host_id
        ];

        if ($id === null) {
            $data['id'] = Uuid::uuid6()->toString();
        } else {
            $data['id'] = $id;
        }

        $task = array_merge($task, $data);

        $tasks = $this->getTasks();
        $tasks[] = $task;

        Cache::put($cache_key, $tasks, 600);
        return $task;
    }
}
