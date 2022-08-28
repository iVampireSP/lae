<?php

use App\Http\Controllers\User;
use App\Http\Controllers\Remote;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\TaskController;

Route::name('api.')->middleware(['api', 'auth:sanctum'])->group(function () {
    // Route::apiResource('users', Controllers\User\UserController::class);


    Route::apiResource('drops', User\DropController::class);

    Route::get('tasks', [TaskController::class, 'index']);

    Route::apiResource('work-orders', User\WorkOrder\WorkOrderController::class);
    Route::apiResource('work-orders.replies', User\WorkOrder\ReplyController::class);

    // 调用远程 API
    // Route::post('hosts/{host}/func/{func}', [Remote\CallController::class, 'host'])->name('host.call');
    Route::post('/modules/{module}', [Remote\ModuleController::class, 'call'])->name('module.call');

});
