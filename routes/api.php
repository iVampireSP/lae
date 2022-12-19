<?php

use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\HostController;
use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ReplyController;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexController::class)->withoutMiddleware('auth:sanctum');
Route::get('user', [UserController::class, 'index']);
Route::get('users', [UserController::class, 'index']);
Route::get('servers', ServerController::class);
Route::get('modules', [ModuleController::class, 'index']);
Route::resource('tasks', TaskController::class)->only(['index', 'show']);

Route::get('forum/announcements', [ForumController::class, 'pinned']);
Route::get('forum/pinned', [ForumController::class, 'announcements']);

Route::get('hosts/usages', [HostController::class, 'usages']);
Route::apiResource('hosts', HostController::class);

// Route::apiResource('balances', BalanceController::class)->only(['index', 'store']);
// Route::get('balances/transactions', [BalanceController::class, 'transactions']);
// Route::get('balances/drops', [BalanceController::class, 'drops']);

Route::apiResource('work-orders', WorkOrderController::class)->only(['index', 'store', 'show', 'update']);

Route::apiResource('work-orders.replies', ReplyController::class)->only(['index', 'store']);


// 匹配 modules/{module} 的路由，正则匹配斜杠
Route::any('modules/{module}/{path?}', [ModuleController::class, 'call'])
    ->where('path', '.*');

