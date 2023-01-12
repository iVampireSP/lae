<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $task = (new Task)->with('host')->latest()->where('user_id', $request->user()->id);

        if ($request->filled('status')) {
            $task->where('status', $request->input('status'));
        }

        $tasks = $task->limit(20)->get();

        return $this->success($tasks);
    }

    public function show(Task $task): JsonResponse
    {
        if ($task->user_id !== auth('sanctum')->id()) {
            return $this->error('无权查看');
        }

        return $this->success($task);
    }
}
