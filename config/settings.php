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
        'type' => env('NODE_TYPE', 'master'),
        'id' => env('NODE_ID'),
        'ip' => env('NODE_IP'),
    ]
];
