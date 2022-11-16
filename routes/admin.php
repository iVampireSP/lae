<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
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
    Route::resource('users', UserController::class);
});
