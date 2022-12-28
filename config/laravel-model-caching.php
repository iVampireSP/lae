<?php

return [
    'cache-prefix' => '',

    'enabled' => env('MODEL_CACHE_ENABLED', true),

    'use-database-keying' => env('MODEL_CACHE_USE_DATABASE_KEYING', true),

    'store' => 'redis_2',
];
