<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::name('admin.')->middleware(['admin'])->group(function () {
    Route::apiResource('admins', Admin\Admin\AdminController::class);

    Route::apiResource('users', Admin\User\UserController::class);
    Route::apiResource('users.drops', Admin\User\DropController::class);
    Route::apiResource('users.work-orders', Admin\WorkOrder\WorkOrderController::class);
    Route::apiResource('users.hosts', Admin\Host\HostController::class);

    // Route::apiResource('drops', Admin\User\DropController::class);

    // work orders
    // Route::apiResource('work-orders', Admin\WorkOrder\WorkOrderController::class);



    // Route::resource('users.fields', v1\User\FieldsController::class)->only(['index']);

    // sub routes for clients
    // Route::apiResource('clients', Admin\Client\ClientController::class);

    // clients.balance
    // Route::apiResource('clients.balances', Admin\Client\BalanceController::class);
});
