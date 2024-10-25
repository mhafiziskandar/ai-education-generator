<?php

return [
    'path' => env('FILAMENT_PATH', 'admin'),
    'home_url' => '/',
    'domain' => env('FILAMENT_DOMAIN'),
    'brand' => env('APP_NAME'),
    'auth' => [
        'guard' => env('FILAMENT_AUTH_GUARD', 'web'),
    ],
    'pages' => [
        'namespace' => 'App\\Filament\\Pages',
    ],
    'resources' => [
        'namespace' => 'App\\Filament\\Resources',
    ],
    'widgets' => [
        'namespace' => 'App\\Filament\\Widgets',
    ],
];