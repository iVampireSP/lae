<?php

$trusted_proxies = [];

if (env('TRUSTED_PROXIES')) {
    $trusted_proxies = explode(',', env('TRUSTED_PROXIES'));
}

return [
    'proxies' => $trusted_proxies,
];
