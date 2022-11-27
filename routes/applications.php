<?php

use App\Http\Controllers\Application\MqttAuthController;

// 登录
Route::post('mqtt/authentication', [MqttAuthController::class, 'authentication'])->name('mqtt.authentication');
// 授权
Route::post('mqtt/authorization', [MqttAuthController::class, 'authorization'])->name('mqtt.authorization');
