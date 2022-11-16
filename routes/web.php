<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BalanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::view('banned', 'banned')->name('banned');

    Route::post('/newToken', [AuthController::class, 'newToken'])->name('newToken');
    Route::delete('/deleteAll', [AuthController::class, 'deleteAll'])->name('deleteAll');

    // logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


    Route::resource('/balances', BalanceController::class);


});

Route::prefix('auth')->group(function () {
    Route::get('redirect', [AuthController::class, 'redirect'])->name('login');
    Route::get('callback', [AuthController::class, 'callback'])->name('callback');
});

Route::get('/', [AuthController::class, 'index'])->name('index');
Route::view('not_verified', 'not_verified')->name('not_verified');


Route::get('/balances/{balances}', [BalanceController::class, 'show'])->name('balances.balances.show');
Route::get('/balances/alipay/notify', [BalanceController::class, 'notify'])->name('balances.alipay.notify');

