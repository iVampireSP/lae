<?php

use Illuminate\Support\Carbon;

function now($timezone = null)
{
    return Carbon::now($timezone);
}
