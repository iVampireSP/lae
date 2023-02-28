<?php

use App\Http\Controllers\Api\BalanceController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\HostController;
use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ReplyController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\Public\AuthRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'index'])->withoutMiddleware('auth:sanctum');
Route::get('/birthdays', [IndexController::class, 'birthdays']);

Route::get('users', [UserController::class, 'index']);

Route::get('user', [UserController::class, 'index']);
Route::patch('user', [UserController::class, 'update']);

Route::get('maintenances', MaintenanceController::class);

Route::resource('balances', BalanceController::class);

Route::get('nodes', [IndexController::class, 'nodes']);
Route::get('modules', [ModuleController::class, 'index']);
Route::get('servers/{module}', [ModuleController::class, 'servers']);

Route::resource('tasks', TaskController::class)->only(['index', 'show']);

// Route::get('forum/announcements', [ForumController::class, 'pinned']);
// Route::get('forum/pinned', [ForumController::class, 'announcements']);
Route::get('forum/{tag}', [ForumController::class, 'tag']);

Route::get('hosts/usages', [HostController::class, 'usages']);
Route::apiResource('hosts', HostController::class);

Route::apiResource('work-orders', WorkOrderController::class)->only(['index', 'store']);
Route::apiResource('subscriptions', SubscriptionController::class)->middleware('resource_owner:subscription');

Route::withoutMiddleware('auth:sanctum')->prefix('work-orders')->group(function () {
    Route::get('{workOrder:uuid}', [WorkOrderController::class, 'show']);

    Route::match(['patch', 'put'], '{workOrder:uuid}', [WorkOrderController::class, 'update']);

    Route::get('{workOrder:uuid}/replies', [ReplyController::class, 'index']);
    Route::post('{workOrder:uuid}/replies', [ReplyController::class, 'store']);
});

Route::any('modules/{module}/{path?}', [ModuleController::class, 'call'])
    ->where('path', '.*');

Route::post('auth_request', [AuthRequestController::class, 'store']);
Route::get('auth_request/{token}', [AuthRequestController::class, 'show']);
