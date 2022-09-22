<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Facades\Broadcast;

$router->get('/users', [
    'uses' => 'UserController@index'
]);

$router->get('/servers', [
    'uses' => 'ServerController'
]);

$router->group(['prefix' => 'hosts'], function () use ($router) {
    $router->get('/', [
        'uses' => 'User\HostController@index'
    ]);
    $router->get('/usages', [
        'uses' => 'User\HostController@usages'
    ]);
    $router->patch('/{host}', [
        'uses' => 'User\HostController@update'
    ]);
    $router->delete('/{host}', [
        'uses' => 'User\HostController@destroy'
    ]);
});


$router->group(['prefix' => 'balances'], function () use ($router) {
    $router->get('/', [
        'uses' => 'User\BalanceController@index'
    ]);

    $router->post('/', [
        'uses' => 'User\BalanceController@store'
    ]);


    $router->get('/transactions', [
        'uses' => 'User\BalanceController@transactions'
    ]);

    $router->get('/drops', [
        'uses' => 'User\BalanceController@drops'
    ]);
});

$router->get('/tasks', [
    'uses' => 'User\TaskController'
]);

$router->group(['prefix' => 'work-orders'], function () use ($router) {
    $router->get('/', [
        'uses' => 'User\WorkOrder\WorkOrderController@index'
    ]);
    $router->post('/', [
        'uses' => 'User\WorkOrder\WorkOrderController@store'
    ]);
    $router->get('/{workOrder}', [
        'uses' => 'User\WorkOrder\WorkOrderController@show'
    ]);
    $router->patch('/{workOrder}', [
        'uses' => 'User\WorkOrder\WorkOrderController@update'
    ]);
    $router->delete('/{workOrder}', [
        'uses' => 'User\WorkOrder\WorkOrderController@destroy'
    ]);

    $router->get('/{workOrder}/replies', [
        'uses' => 'User\WorkOrder\ReplyController@index'
    ]);
    $router->post('/{workOrder}/replies', [
        'uses' => 'User\WorkOrder\ReplyController@store'
    ]);

    // $router->group(['prefix' => ''], function () use ($router) {

    //     // $router->patch('/{reply}', [
    //     //     'uses' => 'User\WorkOrder\ReplyController@update'
    //     // ]);
    //     // $router->delete('/{reply}', [
    //     //     'uses' => 'User\WorkOrder\ReplyController@destroy'
    //     // ]);
    // });
});


$router->group(['prefix' => 'forum'], function () use ($router) {
    $router->get('/announcements', [
        'uses' => 'ForumController@announcements'
    ]);

    $router->get('/pinned', [
        'uses' => 'ForumController@pinned'
    ]);
});

$router->group(['prefix' => 'modules/{module}'], function () use ($router) {
    $controller = 'Remote\ModuleController@call';
    $router->get('/{route:.*}/', $controller);
    $router->post('/{route:.*}/', $controller);
    $router->put('/{route:.*}/', $controller);
    $router->patch('/{route:.*}/', $controller);
    $router->delete('/{route:.*}/', $controller);
});


$router->get('broadcasting/auth', ['uses' => 'BroadcastController@authenticate']);
$router->post('broadcasting/auth', ['uses' => 'BroadcastController@authenticate']);
