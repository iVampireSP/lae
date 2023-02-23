<?php

use App\Http\Controllers\Web\AffiliateController;
use App\Http\Controllers\Web\Auth\ConfirmPasswordController;
use App\Http\Controllers\Web\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Auth\ResetPasswordController;
use App\Http\Controllers\Web\Auth\VerificationController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BalanceController;
use App\Http\Controllers\Web\HostController;
use App\Http\Controllers\Web\MaintenanceController;
use App\Http\Controllers\Web\RealNameController;
use App\Http\Controllers\Web\TransferController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'index'])->middleware('banned')->name('index');

Route::prefix('auth')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    Route::get('password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('password/confirm', [ConfirmPasswordController::class, 'confirm']);

    Route::get('email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
});

Route::middleware(['auth:web', 'banned', 'verified'])->group(
    function () {
        /* Start 账户区域 */
        Route::withoutMiddleware(['banned', 'verified'])->group(
            function () {
                Route::view('banned', 'banned')->withoutMiddleware(['banned', 'verified'])->name('banned');
            }
        );

        Route::middleware(['real_named'])->group(
            function () {
                Route::get('confirm_redirect', [AuthController::class, 'confirm_redirect'])->middleware('real_named')->name('confirm_redirect');
                Route::post('newToken', [AuthController::class, 'newToken'])->middleware('real_named')->name('token.new');
            }
        );

        Route::delete('deleteAll', [AuthController::class, 'deleteAll'])->name('token.delete_all');

        Route::withoutMiddleware('verified')->patch('user', [AuthController::class, 'update'])->name('users.update');
        Route::post('sudo/exit', [AuthController::class, 'exitSudo'])->name('sudo.exit');
        /* End 账户区域 */

        /* Start 主机 */
        Route::resource('hosts', HostController::class)->only(['index', 'update', 'destroy']);
        Route::post('hosts/{host}/renew', [HostController::class, 'renew'])->name('hosts.renew');
        /* End 主机 */

        /* Start 财务 */
        Route::get('transactions', [BalanceController::class, 'transactions'])->name('transactions');

        Route::resource('balances', BalanceController::class)->except('show');
        Route::get('/balances/{balance:order_id}', [BalanceController::class, 'show'])->withoutMiddleware('auth')->name('balances.show');

        Route::middleware(['real_named', 'password.confirm'])->group(
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

        /* Start 推介 */
        Route::resource('affiliates', AffiliateController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::middleware('guest')->withoutMiddleware(['verified', 'auth:web'])->get('affiliates/{affiliate:uuid}', [AffiliateController::class, 'show'])->name('affiliates.show');
        /* End 推介 */

        /* Start 匿名登录 */
        Route::get('auth_request/{auth_request}', [AuthController::class, 'showAuthRequest'])->withoutMiddleware(['auth:web', 'verified'])->name('auth_request.show');
        Route::post('auth_request', [AuthController::class, 'storeAuthRequest'])->name('auth_request.store');
        /* End 匿名登录 */
    }
);

// 联系我们
Route::view('contact', 'contact')->name('contact');

// 支付回调
Route::match(['get', 'post'], '/balances/notify/{payment}', [BalanceController::class, 'notify'])->name('balances.notify');

// 维护
Route::get('maintenance', MaintenanceController::class)->name('maintenances');
