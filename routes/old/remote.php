<?php

use App\Http\Controllers\Remote;
use Illuminate\Support\Facades\Route;

Route::name('remote.')->middleware(['remote'])->group(function () {
    // Route::apiResource('users', Remote\UserController::class)->only(['show']);

    Route::apiResource('modules', Remote\ModuleController::class)->only(['index']);
    Route::apiResource('servers', \App\Http\Controllers\ServerController::class);
    Route::apiResource('hosts', Remote\Host\HostController::class);
    // Route::patch('hosts/{host}', [Remote\Host\DropController::class, 'update']);
    // Route::patch('tasks', Remote\Host\TaskController::class);
    Route::apiResource('tasks', Remote\Host\TaskController::class);

    Route::apiResource('work-orders', Remote\WorkOrder\WorkOrderController::class);
    Route::apiResource('work-orders.replies', Remote\WorkOrder\ReplyController::class);

    // Route::apiResource('users', Controllers\User\UserController::class);
});
