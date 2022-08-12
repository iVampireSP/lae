<?php

use App\Http\Controllers;
use App\Http\Controllers\Admin\User\DropController;
use App\Models\Admin\Admin;
use Illuminate\Support\Facades\Route;

Route::name('client.')->middleware(['api'])->group(function () {
    // Route::apiResource('users', Controllers\User\UserController::class);

    Route::apiResource('drops', DropController::class);

});
