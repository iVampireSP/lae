<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::name('client.')->middleware(['api'])->group(function () {
    Route::apiResource('users', Admin\User\UserController::class);
    // Route::resource('users.fields', v1\User\FieldsController::class)->only(['index']);

    // sub routes for clients
    Route::apiResource('clients', Admin\Client\ClientController::class);

    // clients.balance
    Route::apiResource('clients.balances', Admin\Client\BalanceController::class);
});
