<?php

use App\Http\Controllers\Modules\BroadcastController;
use App\Http\Controllers\Modules\Host\HostController;
use App\Http\Controllers\Modules\Host\TaskController;
use App\Http\Controllers\Modules\ModuleController;
use App\Http\Controllers\Modules\UserController;
use App\Http\Controllers\Modules\WorkOrder\ReplyController;
use App\Http\Controllers\Modules\WorkOrder\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::get('modules', [ModuleController::class, 'index']);

Route::apiResource('hosts', HostController::class);

Route::apiResource('tasks', TaskController::class);

Route::apiResource('work-orders', WorkOrderController::class);
Route::apiResource('work-orders.replies', ReplyController::class);

// 用户信息
Route::get('users', [UserController::class, 'index']);
Route::get('users/{user}', [UserController::class, 'show']);
Route::post('users/{user}/reduce', [UserController::class, 'reduce']);
Route::get('users/{user}/hosts', [UserController::class, 'hosts']);

Route::post('broadcast/users/{user}', [BroadcastController::class, 'broadcast_to_user']);
Route::post('broadcast/users/{user}', [BroadcastController::class, 'broadcast_to_host']);

// 模块间调用
Route::any('modules/{module}/{path?}', [ModuleController::class, 'exportCall'])
    ->where('path', '.*');
