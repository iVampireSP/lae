<?php

// use App\Models\Task;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});

// Broadcast::channel('tasks.{task}', function ($user, Task $task) {
//     return (int)$user->id === (int)$task->user_id;
// });

Broadcast::channel('servers', function () {
    return true;
});
