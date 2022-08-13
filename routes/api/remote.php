<?php

use App\Http\Controllers;
use App\Http\Controllers\Admin\User\DropController;
use App\Models\Admin\Admin;
use Illuminate\Support\Facades\Route;

Route::name('remote.')->middleware(['api', 'auth:remote'])->group(function () {
    // Route::apiResource('users', Controllers\User\UserController::class);
});
