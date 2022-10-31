<?php

return [
    'wecom' => [
        'robot_hook' => [
            'default' => env('WECOM_ROBOT_HOOK_DEFAULT', ''),
            'billing' => env('WECOM_ROBOT_HOOK_BILLING', ''),
            'cluster_ready' => env('WECOM_ROBOT_HOOK_CLUSTER_READY', ''),
        ],
    ]
];
