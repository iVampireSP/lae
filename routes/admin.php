<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\HostController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\ReplyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware(['auth'])->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::view('/', 'admin.index')->name('index')->middleware('auth:admin');


Route::group([
    'middleware' => 'auth:admin',
], function () {
    Route::resource('users', UserController::class)->only(['index', 'show', 'edit', 'update']);
    Route::resource('modules', ModuleController::class);
    Route::resource('hosts', HostController::class);
    Route::resource('work-orders', WorkOrderController::class);
    Route::resource('work-orders.replies', ReplyController::class);
});
