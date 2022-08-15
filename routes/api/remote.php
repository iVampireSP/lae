<?php

use App\Http\Controllers\Remote;
use Illuminate\Support\Facades\Route;

Route::name('remote.')->middleware(['api'])->group(function () {
    Route::apiResource('modules', Remote\ModuleController::class)->only(['index']);
    Route::apiResource('servers', Remote\ServerController::class);
    Route::patch('hosts/{host}/drops', [Remote\Host\DropController::class, 'update']);
    Route::apiResource('users.tasks', Remote\User\TaskController::class);
    // Route::apiResource('users', Controllers\User\UserController::class);
});
