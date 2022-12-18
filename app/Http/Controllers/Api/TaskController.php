<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::where('user_id', $request->user()->id)->with('host')->latest()->get();
        return $this->success($tasks);
    }

    public function show(Task $task)
    {
        if ($task->user_id !== auth('sanctum')->id()) {
            return $this->error('无权查看');
        }

        return $this->success($task);
    }
}
