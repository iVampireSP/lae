<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\TaskController;
use App\Http\Controllers\Remote\ModuleController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\User\BalanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\User\WorkOrder\ReplyController;
use App\Http\Controllers\User\WorkOrder\WorkOrderController;

Route::name('api.')->middleware(['api', 'auth:sanctum'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::get('servers', ServerController::class);


    Route::apiResource('balances', BalanceController::class)->only(['index', 'store']);
    // Route::apiResource('drops', DropController::class);

    Route::get('tasks', TaskController::class);

    Route::apiResource('work-orders', WorkOrderController::class);
    Route::apiResource('work-orders.replies', ReplyController::class);

    // 调用远程 API
    Route::any('/modules/{module}', [ModuleController::class, 'call'])->name('module.call');
});


Route::get('/pay/return', [BalanceController::class, 'notify'])->name('balances.return');
Route::get('/pay/notify', [BalanceController::class, 'notify'])->name('balances.notify');
