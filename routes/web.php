<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BalanceController;
use App\Http\Controllers\Web\TransferController;
use Illuminate\Support\Facades\Route;


Route::get('/', [AuthController::class, 'index'])->name('index')->middleware('banned');


Route::prefix('auth')->group(function () {
    Route::get('redirect', [AuthController::class, 'redirect'])->name('login');
    Route::get('callback', [AuthController::class, 'callback'])->name('callback');
});


Route::middleware(['auth', 'banned'])->group(function () {
    Route::view('banned', 'banned')->name('banned')->withoutMiddleware('banned');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout')->withoutMiddleware('banned');

    Route::get('confirm_redirect', [AuthController::class, 'confirm_redirect'])->name('confirm_redirect');
    Route::post('newToken', [AuthController::class, 'newToken'])->name('newToken');
    Route::delete('deleteAll', [AuthController::class, 'deleteAll'])->name('deleteAll');

    Route::get('transactions', [BalanceController::class, 'transactions'])->name('transactions');

    Route::resource('balances', BalanceController::class)->except('show');
    Route::get('/balances/{balance:order_id}', [BalanceController::class, 'show'])->name('balances.show')->withoutMiddleware('auth');

    Route::get('transfer', [TransferController::class, 'index'])->name('transfer');
    Route::post('transfer', [TransferController::class, 'transfer']);
});


Route::view('contact', 'contact')->name('contact');

Route::view('not_verified', 'not_verified')->name('not_verified');
Route::match(['get', 'post'], '/balances/notify/{payment}', [BalanceController::class, 'notify'])->name('balances.notify');


