<?php

namespace App\Http\Controllers\User;

use App\Models\User\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    public function __invoke()
    {
        //
        $tasks = Task::user()->get();

        return $this->success($tasks);
    }
}
