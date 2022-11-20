<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\HostController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware(['auth'])->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [HomeController::class, 'index'])->name('index')->middleware('auth:admin');

Route::group([
    'middleware' => 'auth:admin',
], function () {
    Route::resource('users', UserController::class)->only(['index', 'show', 'edit', 'update']);
    Route::resource('modules', ModuleController::class);
    Route::resource('hosts', HostController::class)->only(['index', 'edit', 'update', 'destroy']);
    Route::resource('work-orders', WorkOrderController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);

    Route::view('commands', 'admin.commands')->name('commands');
});
