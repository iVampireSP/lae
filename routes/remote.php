<?php

/** @var \Laravel\Lumen\Routing\Router $router */


$router->get('modules', [
    'uses' => 'ModuleController@index'
]);

$router->get('servers', [
    'uses' => '\App\Http\Controllers\ServerController'
]);

$router->group(['prefix' => 'hosts'], function () use ($router) {
    $router->post('/', [
        'uses' => 'Host\HostController@store'
    ]);
    $router->get('/{host}', [
        'uses' => 'Host\HostController@show'
    ]);
    $router->patch('/{host}', [
        'uses' => 'Host\HostController@update'
    ]);
    $router->delete('/{host}', [
        'uses' => 'Host\HostController@destroy'
    ]);
});


$router->group(['prefix' => 'tasks'], function () use ($router) {
    $router->post('/', [
        'uses' => 'Host\TaskController@store'
    ]);
    $router->get('/{task}', [
        'uses' => 'Host\TaskController@show'
    ]);
    $router->patch('/{task}', [
        'uses' => 'Host\TaskController@update'
    ]);
    $router->delete('/{task}', [
        'uses' => 'Host\TaskController@destroy'
    ]);
});



$router->group(['prefix' => 'work-orders'], function () use ($router) {
    $router->get('/', [
        'uses' => 'WorkOrder\WorkOrderController@index'
    ]);
    $router->post('/', [
        'uses' => 'WorkOrder\WorkOrderController@store'
    ]);
    $router->patch('/{workOrder}', [
        'uses' => 'WorkOrder\WorkOrderController@update'
    ]);
    $router->delete('/{workOrder}', [
        'uses' => 'WorkOrder\WorkOrderController@destroy'
    ]);

    $router->group(['prefix' => '{workOrder}/replies'], function () use ($router) {
        $router->get('/', [
            'uses' => 'WorkOrder\ReplyController@index'
        ]);
        $router->post('/', [
            'uses' => 'WorkOrder\ReplyController@store'
        ]);
        $router->patch('/{reply}', [
            'uses' => 'WorkOrder\ReplyController@update'
        ]);
        $router->delete('/{reply}', [
            'uses' => 'WorkOrder\ReplyController@destroy'
        ]);
    });
});


// 模块间调用

$router->group(['prefix' => 'modules/{module}'], function () use ($router) {
    $controller = 'ModuleController@exportCall';
    $router->get('/{route:.*}/', $controller);
    $router->post('/{route:.*}/', $controller);
    $router->put('/{route:.*}/', $controller);
    $router->patch('/{route:.*}/', $controller);
    $router->delete('/{route:.*}/', $controller);
});


// 用户信息
$router->get('users', [
    'uses' => '\App\Http\Controllers\Remote\UserController@index'
]);

$router->get('users/{user}', [
    'uses' => '\App\Http\Controllers\Remote\UserController@show'
]);

$router->post('users/{user}/reduce', [
    'uses' => '\App\Http\Controllers\Remote\UserController@reduce'
]);

$router->get('users/{user}/hosts', [
    'uses' => '\App\Http\Controllers\Remote\UserController@hosts'
]);


$router->post('broadcast/users/{user}', [
    'uses' => '\App\Http\Controllers\Remote\BroadcastController@broadcast_to_user'
]);

$router->post('broadcast/hosts/{host}', [
    'uses' => '\App\Http\Controllers\Remote\BroadcastController@broadcast_to_host'
]);
