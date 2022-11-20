<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __invoke(Request $request)
    {
        $tasks = Task::where('user_id', $request->user()->id)->with('host')->latest()->get();
        return $this->success($tasks);
    }
}
