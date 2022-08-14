<?php

use App\Http\Controllers\Remote;
use Illuminate\Support\Facades\Route;

Route::name('remote.')->middleware(['api', 'auth:remote'])->group(function () {
    Route::apiResource('providers', Remote\ProviderController::class)->only(['index']);
    // Route::apiResource('users', Controllers\User\UserController::class);
});
