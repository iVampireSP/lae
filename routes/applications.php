<?php

use App\Http\Controllers\Application\ModuleController;
use App\Http\Controllers\Application\MqttAuthController;
use App\Http\Controllers\Application\UserController;
use Illuminate\Support\Facades\Route;

// MQTT Auth
Route::prefix('mqtt')->as('mqtt.')->group(function () {
    // 登录
    Route::post('authentication', [MqttAuthController::class, 'authentication'])->name('authentication');
    // 授权
    Route::post('authorization', [MqttAuthController::class, 'authorization'])->name('authorization');
});

// Modules
Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
Route::get('modules/{module}', [ModuleController::class, 'show'])->name('modules.show');

Route::resource('users', UserController::class)->only(['index', 'show', 'store']);

Route::get('token/{token}', [UserController::class, 'auth']);
