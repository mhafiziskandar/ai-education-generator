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
        'path' => app_path('Filament/Pages'),
        'register' => [
            \App\Filament\Admin\Pages\Dashboard::class,
        ],
    ],
    'resources' => [
        'namespace' => 'App\\Filament\\Resources',
    ],
    'widgets' => [
        'namespace' => 'App\\Filament\\Widgets',
    ],
];