<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

function now($timezone = null)
{
    return Carbon::now($timezone);
}
