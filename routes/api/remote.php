<?php

use App\Http\Controllers\Remote;
use Illuminate\Support\Facades\Route;

Route::name('remote.')->middleware(['api'])->group(function () {
    Route::apiResource('modules', Remote\ModuleController::class)->only(['index']);
    Route::apiResource('servers', Remote\ServerController::class);
    Route::apiResource('hosts', Remote\Host\HostController::class);
    Route::patch('hosts/{host}/drops', [Remote\Host\DropController::class, 'update']);
    Route::apiResource('hosts.tasks', Remote\Host\TaskController::class);

    Route::apiResource('work-orders', Remote\WorkOrder\WorkOrderController::class);
    Route::apiResource('work-orders.replies', Remote\WorkOrder\ReplyController::class);

    // Route::apiResource('users', Controllers\User\UserController::class);
});
