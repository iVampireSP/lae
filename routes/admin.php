<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\HostController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserGroupController;
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
    Route::resource('admins', AdminController::class)->except('show');

    Route::resource('users', UserController::class)->only(['index', 'show', 'edit', 'update']);

    Route::resource('modules', ModuleController::class);

    Route::get('modules/{module}/allows', [ModuleController::class, 'allows'])->name('modules.allows');
    Route::post('modules/{module}/allows', [ModuleController::class, 'allows_store'])->name('modules.allows.store');
    Route::delete('modules/{module}/allows/{allow}', [ModuleController::class, 'allows_destroy'])->name('modules.allows.destroy');

    Route::resource('applications', ApplicationController::class);
    Route::resource('hosts', HostController::class)->only(['index', 'edit', 'update', 'destroy']);
    Route::resource('work-orders', WorkOrderController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);

    Route::resource('user-groups', UserGroupController::class);

    Route::resource('devices', DeviceController::class)->only(['index', 'destroy']);


    Route::view('commands', 'admin.commands')->name('commands');

    Route::get('transactions', [HomeController::class, 'transactions'])->name('transactions');
});
