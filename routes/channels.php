<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});

Broadcast::channel('tasks.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});


Broadcast::channel('work-orders.{uuid}', function () {
    return true;
});


Broadcast::channel('servers', function () {
    return true;
});
