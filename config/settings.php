<?php

return [
    'wecom' => [
        'robot_hook' => [
            'default' => env('WECOM_ROBOT_HOOK_DEFAULT', ''),
            'billing' => env('WECOM_ROBOT_HOOK_BILLING', ''),
            'cluster_ready' => env('WECOM_ROBOT_HOOK_CLUSTER_READY', ''),
        ],
    ],
    'user_groups' => [
        'birthday_group_id' => env('USER_GROUP_BIRTHDAY', 1),
    ],
    'dashboard' => [
        'base_url' => env('DASHBOARD_BASE_URL', 'https://dash.laecloud.com'),
        'birthday_path' => env('DASHBOARD_BIRTHDAY_PATH', '/birthdays'),
    ],
    'node' => [
        'type' => env('NODE_TYPE', 'slave'),
        'id' => env('NODE_ID'),
        'ip' => env('NODE_IP'),
    ],
    'roadrunner' => [
        'version' => env('ROADRUNNER_VERSION', '2.12.1'),
    ],
    'supports' => [
        'real_name' => [
            'code' => env('SUPPORT_REAL_NAME_APP_CODE'),
            'min_age' => env('SUPPORT_REAL_NAME_MIN_AGE', 13),
            'max_age' => env('SUPPORT_REAL_NAME_MAX_AGE', 60),
        ],
    ],
    'forum' => [
        'base_url' => env('FORUM_BASEURL'),
    ],
];
