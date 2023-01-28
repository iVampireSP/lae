<?php

use Adapterman\Adapterman;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

Adapterman::init();

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$http_worker = (new Worker('http://0.0.0.0:8080'));
$http_worker->count = 8;
$http_worker->name = 'AdapterMan';

$http_worker->onMessage = static function (TcpConnection $connection) use ($kernel) {
    ob_start();

    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );


    $response->send();

    $kernel->terminate($request, $response);

    $connection->send(ob_get_clean());
};

Worker::runAll();
