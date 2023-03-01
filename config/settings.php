<?php

return [
    'billing' => [
        'commission' => '0.1',
        // 推介佣金比例
        'commission_referral' => '0.8',
    ],
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
        'work_order_path' => env('DASHBOARD_WORK_ORDER_PATH', '/work-orders'),
    ],
    'node' => [
        'type' => env('NODE_TYPE', 'slave'),
        'id' => env('NODE_ID'),
        'ip' => env('NODE_IP'),
        'rpc_port' => env('NODE_RPC_PORT', 6001),
    ],
    'roadrunner' => [
        'version' => env('ROADRUNNER_VERSION', '2.12.2'),
    ],
    'supports' => [
        'real_name' => [
            'code' => env('SUPPORT_REAL_NAME_APP_CODE'),
            'min_age' => env('SUPPORT_REAL_NAME_MIN_AGE', 13),
            'max_age' => env('SUPPORT_REAL_NAME_MAX_AGE', 60),
            'price' => env('SUPPORT_REAL_NAME_PRICE', 0.7),
        ],
    ],
    'forum' => [
        'base_url' => env('FORUM_BASEURL'),
    ],
];
