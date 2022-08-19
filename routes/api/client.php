<?php

use App\Http\Controllers\User;
use App\Http\Controllers\Remote;
use Illuminate\Support\Facades\Route;

Route::name('client.')->middleware(['api', 'auth:sanctum'])->group(function () {
    // Route::apiResource('users', Controllers\User\UserController::class);

    Route::apiResource('/modules/{module}/hosts', User\HostController::class);

    Route::apiResource('drops', User\DropController::class);

    Route::apiResource('work-orders', User\WorkOrder\WorkOrderController::class);
    Route::apiResource('work-orders.replies', User\WorkOrder\ReplyController::class);

    // 调用远程 API
    Route::post('hosts/{host}/func/{func}', [Remote\CallController::class, 'host'])->name('host.call');
    Route::post('/modules/{module}/func/{func}', [Remote\CallController::class, 'module'])->name('module.call');

});
