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
    $router->get('/{tasks}', [
        'uses' => 'Host\TaskController@show'
    ]);
    $router->patch('/{tasks}', [
        'uses' => 'Host\TaskController@update'
    ]);
    $router->delete('/{tasks}', [
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
