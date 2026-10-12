<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Filament Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for configuring the behavior and appearance of Filament's
    | admin panel and other packages.
    |
    */

    'admin_panel' => [

        /*
        |----------------------------------------------------------------------
        | Resources
        |----------------------------------------------------------------------
        |
        | The following is the configuration for registering Filament resources.
        |
        */

        'resources' => [
            'namespace' => 'App\\Filament\\Resources',
            'path' => app_path('Filament/Resources'),
            'register_navigation_items' => true,
        ],

        /*
        |----------------------------------------------------------------------
        | Pages
        |----------------------------------------------------------------------
        |
        | The following is the configuration for registering Filament pages.
        |
        */

        'pages' => [
            'namespace' => 'App\\Filament\\Pages',
            'path' => app_path('Filament/Pages'),
            'register_navigation_items' => true,
        ],

        /*
        |----------------------------------------------------------------------
        | Widgets
        |----------------------------------------------------------------------
        |
        | The following is the configuration for registering Filament widgets.
        |
        */

        'widgets' => [
            'namespace' => 'App\\Filament\\Widgets',
            'path' => app_path('Filament/Widgets'),
        ],

    ],

];
