<?php

use Illuminate\Support\Carbon;

function now($timezone = null)
{
    return Carbon::now($timezone);
}

function cluster_task_id()
{
    return uniqid(config('app.instance_id') . '_');
}

function cluster_run_wait($command, $data = [])
{
    $redis = app('redis')->connection('cluster_ready');

    $task_id = cluster_task_id();

    $redis->publish('cluster_ready', json_encode([
        'task_id' => $task_id,
        'type' => $command,
        'data' => $data,
    ]));

    // 等待结果，最多等待 10 秒
    $result = $redis->blpop("cluster:task:{$task_id}", 10);

    if ($result) {
        return json_decode($result[1], true);
    } else {
        throw new \Exception('任务执行超时');
    }
}


// function nodes()
// {
//     return Cache::remember('nodes', 60, function () {

//         $collection = collect(['taylor', 'abigail', null])->map(function ($name) {
//             return strtoupper($name);
//         })->reject(function ($name) {
//             return empty($name);
//         });
//     });
// }
