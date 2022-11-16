<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BalanceController;
use App\Http\Controllers\Web\TransferController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'banned'])->group(function () {
    Route::view('banned', 'banned')->name('banned')->withoutMiddleware('banned');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout')->withoutMiddleware('banned');

    Route::post('newToken', [AuthController::class, 'newToken'])->name('newToken');
    Route::delete('deleteAll', [AuthController::class, 'deleteAll'])->name('deleteAll');



    Route::get('transactions', [BalanceController::class, 'transactions'])->name('transactions');
    Route::resource('balances', BalanceController::class);

    Route::get('transfer', [TransferController::class, 'index'])->name('transfer');
    Route::post('transfer', [TransferController::class, 'transfer']);


});

Route::prefix('auth')->group(function () {
    Route::get('redirect', [AuthController::class, 'redirect'])->name('login');
    Route::get('callback', [AuthController::class, 'callback'])->name('callback');
});

Route::get('/', [AuthController::class, 'index'])->name('index')->middleware('banned');
Route::view('not_verified', 'not_verified')->name('not_verified');


Route::get('/balances/{balances}', [BalanceController::class, 'show'])->name('balances.balances.show');
Route::get('/balances/alipay/notify', [BalanceController::class, 'notify'])->name('balances.alipay.notify');

