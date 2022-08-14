<?php

use App\Http\Controllers\Remote;
use Illuminate\Support\Facades\Route;

Route::name('remote.')->middleware(['api'])->group(function () {
    Route::apiResource('providers', Remote\ModuleController::class)->only(['index']);
    Route::apiResource('servers', Remote\ServerController::class);
    // Route::apiResource('users', Controllers\User\UserController::class);
});
