<?php

use App\Http\Controllers\Admin\AuthController;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware(['auth'])->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::view('/', 'admin.index')->name('index')->middleware('auth:admin');


Route::group([
    'middleware' => 'auth:admins',
], function () {
    // Route::resource('merchants', MerchantController::class);
});
