<?php

return [
    'alipay' => [
        'app_id' => env('ALIPAY_APP_ID'),
    ],
    'wepay' => [
        'mch_id' => env('WECHAT_MENCENT_ID'),
        'v2_secret_key' => env('WECHAT_V2_API_KEY'),
        'v3_secret_key' => env('WECHAT_V3_API_KEY'),
    ]
];
