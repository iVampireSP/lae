<?php

use App\Http\Controllers\Api\BalanceController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::get('redirect', [AuthController::class, 'redirect'])->name('login');
    Route::get('callback', [AuthController::class, 'callback'])->name('callback');
});

Route::get('/', [AuthController::class, 'index'])->name('index');
Route::view('not_verified', 'not_verified')->name('not_verified');

Route::middleware(['auth'])->group(function () {
    Route::view('banned', 'banned')->name('banned');

    Route::post('/newToken', [AuthController::class, 'newToken'])->name('newToken');
    Route::delete('/deleteAll', [AuthController::class, 'deleteAll'])->name('deleteAll');

    // logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


Route::get('/balance/{balance}', [BalanceController::class, 'show'])->name('balances.pay.show');
Route::get('/pay/return', [BalanceController::class, 'return'])->name('balances.alipay.return');
Route::get('/pay/notify', [BalanceController::class, 'notify'])->name('balances.alipay.notify');


Route::get('/pay', function () {

});
