<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

function now($timezone = null)
{
    return Carbon::now($timezone);
}

function getDrops($user_id)
{
    $cache_key = 'user_drops_' . $user_id;

    $decimal = config('drops.decimal');

    // 计算需要乘以多少
    $multiple = 1;
    for ($i = 0; $i < $decimal; $i++) {
        $multiple *= 10;
    }

    $drops = Cache::get($cache_key);

    // 除以 $multiple
    $drops = $drops / $multiple;

    return $drops;
}

function reduceDrops($user_id, $amount = 0)
{
    $cache_key = 'user_drops_' . $user_id;

    $decimal = config('drops.decimal');

    // 计算需要乘以多少
    $multiple = 1;
    for ($i = 0; $i < $decimal; $i++) {
        $multiple *= 10;
    }

    $month = now()->month;

    Cache::increment('user_' . $user_id . '_month_' . $month . '_drops', $amount);

    $amount = $amount * $multiple;

    $drops = Cache::decrement($cache_key, $amount);

    return $drops;
}


function addDrops($user_id, $amount = 0)
{
    $cache_key = 'user_drops_' . $user_id;

    $decimal = config('drops.decimal');

    // 计算需要乘以多少
    $multiple = 1;
    for ($i = 0; $i < $decimal; $i++) {
        $multiple *= 10;
    }

    $amount = $amount * $multiple;

    $drops = Cache::increment($cache_key, $amount);

    return $drops;
}
