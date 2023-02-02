<?php

use App\Http\Controllers\Module\AuthRequestController;
use App\Http\Controllers\Module\BroadcastController;
use App\Http\Controllers\Module\DeviceController;
use App\Http\Controllers\Module\HostController;
use App\Http\Controllers\Module\ModuleController;
use App\Http\Controllers\Module\ReplyController;
use App\Http\Controllers\Module\TaskController;
use App\Http\Controllers\Module\UserController;
use App\Http\Controllers\Module\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::get('modules', [ModuleController::class, 'index']);
Route::get('/', [ModuleController::class, 'index']);

Route::apiResource('hosts', HostController::class);

Route::apiResource('tasks', TaskController::class);

Route::apiResource('work-orders', WorkOrderController::class);
Route::apiResource('work-orders.replies', ReplyController::class);

// 用户信息
Route::resource('users', UserController::class)->only(['index', 'show', 'update']);

Route::get('token/{token}', [UserController::class, 'auth']);
Route::get('users/{user}/hosts', [UserController::class, 'hosts']);

Route::post('broadcast/users/{user}', [BroadcastController::class, 'broadcast_to_user']);
// Route::post('broadcast/hosts/{host}', [BroadcastController::class, 'broadcast_to_host']);

// MQTT
Route::get('devices', [DeviceController::class, 'index']);
Route::delete('devices', [DeviceController::class, 'destroy']);

// 模块间调用
Route::any('modules/{module}/{path?}', [ModuleController::class, 'exportCall'])
    ->where('path', '.*');

// 认证请求
Route::post('auth_request', [AuthRequestController::class, 'store']);
Route::get('auth_request/{token}', [AuthRequestController::class, 'show']);
