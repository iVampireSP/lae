<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;

class TaskController extends Controller
{
    public function __invoke()
    {
        //
        $tasks = (new Task())->getCurrentUserTasks();

        return $this->success($tasks);
    }
}
