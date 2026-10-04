<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Modules Root Path & Namespace
    |--------------------------------------------------------------------------
    |
    | By default, domain modules live in the root `modules/` directory
    | and map to the `Modules\` PSR-4 namespace.
    |
    */
    'path' => base_path('modules'),

    'namespace' => 'Modules\\',

    /*
    |--------------------------------------------------------------------------
    | Architecture Layout Preset
    |--------------------------------------------------------------------------
    |
    | "ddd": 4-Layer Domain-Driven Design (Domain, Application, Interface, Infrastructure, Database)
    | "flat": Legacy flat structure (Actions, Queries, Controllers, Models, etc.)
    |
    */
    'layout' => 'ddd',

    /*
    |--------------------------------------------------------------------------
    | Ignored Directories
    |--------------------------------------------------------------------------
    |
    | Directory names inside the modules path to ignore during auto-discovery.
    |
    */
    'ignore' => [
        'node_modules',
        'vendor',
        '.git',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery Settings
    |--------------------------------------------------------------------------
    |
    | Enable or disable auto-discovery features across all detected modules.
    |
    */
    'autodiscover' => [
        'providers' => true,
        'routes' => true,
        'migrations' => true,
        'views' => true,
        'configs' => true,
        'factories' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Routing Configuration
    |--------------------------------------------------------------------------
    |
    | Default middleware stacks applied to module routes.
    |
    */
    'routing' => [
        'web_middleware' => ['web'],
        'api_middleware' => ['api'],
        'api_prefix' => 'api',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Cache File
    |--------------------------------------------------------------------------
    |
    | The path where compiled module manifest is stored in production
    | when running `php artisan module:cache`.
    |
    */
    'cache_path' => base_path('bootstrap/cache/modules.php'),

    /*
    |--------------------------------------------------------------------------
    | Custom Stubs Path
    |--------------------------------------------------------------------------
    |
    | If you want to customize module stubs, publish them and specify path here.
    | If null, the package's default stubs will be used.
    |
    */
    'stubs_path' => null,
];
