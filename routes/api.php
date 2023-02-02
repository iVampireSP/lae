<?php

use App\Http\Controllers\Api\BalanceController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\HostController;
use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ReplyController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'index'])->withoutMiddleware('auth:sanctum');
Route::get('/birthdays', [IndexController::class, 'birthdays']);

Route::get('users', [UserController::class, 'index']);

Route::get('user', [UserController::class, 'index']);
Route::patch('user', [UserController::class, 'update']);

Route::resource('balances', BalanceController::class);

Route::get('nodes', [IndexController::class, 'nodes']);
Route::get('modules', [ModuleController::class, 'index']);
Route::get('servers/{module}', [ModuleController::class, 'servers']);

Route::resource('tasks', TaskController::class)->only(['index', 'show']);

Route::get('forum/announcements', [ForumController::class, 'pinned']);
Route::get('forum/pinned', [ForumController::class, 'announcements']);

Route::get('hosts/usages', [HostController::class, 'usages']);
Route::apiResource('hosts', HostController::class);

Route::apiResource('work-orders', WorkOrderController::class)->only(['index', 'store']);

Route::withoutMiddleware('auth:sanctum')->prefix('work-orders')->group(function () {
    Route::get('{workOrder:uuid}', [WorkOrderController::class, 'show']);

    Route::match(['patch', 'put'], '{workOrder:uuid}', [WorkOrderController::class, 'update']);

    Route::get('{workOrder:uuid}/replies', [ReplyController::class, 'index']);
    Route::post('{workOrder:uuid}/replies', [ReplyController::class, 'store']);
});

Route::any('modules/{module}/{path?}', [ModuleController::class, 'call'])
    ->where('path', '.*');
