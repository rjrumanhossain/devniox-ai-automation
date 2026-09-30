<?php

return [
    'domain' => env('TENANT_DOMAIN', parse_url((string) env('APP_URL', ''), PHP_URL_HOST) ?: 'localhost'),

    'reserved_usernames' => [
        'admin',
        'administrator',
        'api',
        'app',
        'assets',
        'billing',
        'dashboard',
        'devniox',
        'help',
        'login',
        'mail',
        'manage',
        'panel',
        'portal',
        'register',
        'root',
        'saas',
        'static',
        'support',
        'system',
        'www',
    ],
];
