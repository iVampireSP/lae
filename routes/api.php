<?php

use User\WorkOrder\ReplyController;
use Illuminate\Support\Facades\Route;
use User\WorkOrder\WorkOrderController;
use App\Http\Controllers\User\DropController;
use App\Http\Controllers\Remote\ModuleController;
use App\Http\Controllers\Admin\User\UserController;

Route::name('api.')->middleware(['api', 'auth:sanctum'])->group(function () {
    Route::apiResource('users', UserController::class);


    Route::apiResource('drops', DropController::class);

    Route::get('tasks', [TaskController::class, 'index']);

    Route::apiResource('work-orders', WorkOrderController::class);
    Route::apiResource('work-orders.replies', ReplyController::class);

    // 调用远程 API
    Route::any('/modules/{module}', [ModuleController::class, 'call'])->name('module.call');

});
