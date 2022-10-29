<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

function now($timezone = null)
{
    return Carbon::now($timezone);
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
