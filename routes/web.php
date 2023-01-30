<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BalanceController;
use App\Http\Controllers\Web\RealNameController;
use App\Http\Controllers\Web\TransferController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'index'])->name('index')->middleware('banned');

Route::prefix('auth')->group(function () {
    Route::get('redirect', [AuthController::class, 'redirect'])->name('login');
    Route::get('callback', [AuthController::class, 'callback'])->name('callback');
});

Route::middleware(['auth', 'banned'])->group(
    function () {
        /* Start 账户区域 */
        Route::view('banned', 'banned')->name('banned')->withoutMiddleware('banned');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout')->withoutMiddleware('banned');

        Route::get('confirm_redirect', [AuthController::class, 'confirm_redirect'])->name('confirm_redirect')->middleware('real_named');
        Route::post('newToken', [AuthController::class, 'newToken'])->name('newToken')->middleware('real_named');

        Route::delete('deleteAll', [AuthController::class, 'deleteAll'])->name('deleteAll');
        /* End 账户区域 */

        /* Start 财务 */
        Route::get('transactions', [BalanceController::class, 'transactions'])->name('transactions');

        Route::resource('balances', BalanceController::class)->except('show');
        Route::get('/balances/{balance:order_id}', [BalanceController::class, 'show'])->name('balances.show')->withoutMiddleware('auth');

        Route::middleware(['real_named'])->group(
            function () {
                Route::get('transfer', [TransferController::class, 'index'])->name('transfer');
                Route::post('transfer', [TransferController::class, 'transfer']);
            }
        );
        /* End 财务 */

        /* Start 实名认证 */
        Route::get('real_name', [RealNameController::class, 'create'])->name('real_name.create');
        Route::post('real_name', [RealNameController::class, 'store'])->name('real_name.store');
        /* End 实名认证 */
    }
);

Route::view('contact', 'contact')->name('contact');

Route::match(['get', 'post'], '/balances/notify/{payment}', [BalanceController::class, 'notify'])->name('balances.notify');
