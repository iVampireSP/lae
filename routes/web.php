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
    $pay = Pay::alipay()->web([
        'out_trade_no' => 'lae-' . time(),
        'total_amount' => 10,
        'subject' => config('app.display_name') . ' 充值',
    ]);

    return $pay;
});

Route::get('/t', function () {
    return Pay::alipay()->transfer([
        'out_biz_no' => '202106051432',
        'trans_amount' => '0.01',
        'product_code' => 'TRANS_ACCOUNT_NO_PWD',
        'biz_scene' => 'DIRECT_TRANSFER',
        'payee_info' => [
            'identity' => '2088622956327844',
            'identity_type' => 'ALIPAY_USER_ID',
            'name' => 'vsjnhi5180'
        ],
    ]);
});
