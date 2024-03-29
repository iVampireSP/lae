<?php

return [
    'callback_uri' => env('OAUTH_REDIRECT'),
    'client_id' => env('OAUTH_CLIENT_ID'),
    'client_secret' => env('OAUTH_CLIENT_SECRET'),
    'oauth_domain' => env('OAUTH_DOMAIN'),
    'oauth_name' => env('OAUTH_NAME', 'LoliArt Account'),
    'oauth_auth_url' => env('OAUTH_DOMAIN') . '/oauth/authorize',
    'oauth_token_url' => env('OAUTH_DOMAIN') . '/oauth/token',
    'oauth_user_url' => env('OAUTH_DOMAIN') . '/api/user',
    'oauth_real_name_url' => env('OAUTH_DOMAIN') . '/api/real-name',
    'scope' => 'realname login user',
];
