<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('auth')->group(function () {
    Route::get('redirect', [Controllers\AuthController::class, 'redirect'])->name('login');
    Route::get('callback', [Controllers\AuthController::class, 'callback'])->name('callback');
});

Route::middleware('auth')->group(function () {
    Route::post('/createApiToken', [Controllers\AuthController::class, 'createApiToken'])->name('createApiToken');
    Route::delete('/invokeAllApiToken', [Controllers\AuthController::class, 'invokeAllApiToken'])->name('invokeAllApiToken');
});

Route::get('/', [Controllers\AuthController::class, 'index'])->name('index');

Route::get('/balances/{balance}', [Controllers\User\BalanceController::class, 'show'])->name('balances.pay.show');


