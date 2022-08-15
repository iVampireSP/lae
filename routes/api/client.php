<?php

use App\Http\Controllers;
use App\Http\Controllers\Admin\User\DropController;
use App\Http\Controllers\User;
use Illuminate\Support\Facades\Route;

Route::name('client.')->middleware(['api', 'auth:sanctum'])->group(function () {
    // Route::apiResource('users', Controllers\User\UserController::class);

    Route::apiResource('drops', DropController::class);

    Route::apiResource('work-orders', User\WorkOrder\WorkOrderController::class);
    Route::apiResource('work-orders.replies', User\WorkOrder\ReplyController::class);

});
