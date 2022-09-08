<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', [
    'uses' => 'IndexController'
]);

$router->get('/balances/{balance}', [
    'as' => 'balances.pay.show',
    'uses' => 'User\BalanceController@show'
]);

$router->get('/pay/return', [
    'as' => 'balances.return',
    'uses' => 'User\BalanceController@return'
]);

$router->get('/pay/notify', [
    'as' => 'balances.notify',
    'uses' => 'User\BalanceController@notify'
]);



// $router->group(['prefix' => 'auth', 'middleware' => 'session'], function () use ($router) {
//     $router->get('redirect', [
//         'as' => 'login',
//         'uses' => 'AuthController@redirect'
//     ]);

//     $router->get('callback', [
//         'as' => 'callback',
//         'uses' => 'AuthController@callback'
//     ]);
// });

// auth controller

// Route::prefix('auth')->group(function () {
//     Route::get('redirect', [Controllers\AuthController::class, 'redirect'])->name('login');
//     Route::get('callback', [Controllers\AuthController::class, 'callback'])->name('callback');
// });

// Route::middleware('auth')->group(function () {
//     Route::post('/createApiToken', [Controllers\AuthController::class, 'createApiToken'])->name('createApiToken');
//     Route::delete('/invokeAllApiToken', [Controllers\AuthController::class, 'invokeAllApiToken'])->name('invokeAllApiToken');
// });


// Route::get('/balances/{balance}', [Controllers\User\BalanceController::class, 'show'])->name('balances.pay.show');




// $router->get('/', []);

// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });
