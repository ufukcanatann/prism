<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */
    'name' => env('APP_NAME', 'PRISM Framework'),

    /*
    |--------------------------------------------------------------------------
    | Framework Version
    |--------------------------------------------------------------------------
    |
    | This value is the version of the PRISM Framework. This value is used
    | throughout the application to display version information.
    |
    */
    'version' => env('FRAMEWORK_VERSION', '3.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes.
    |
    */
    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */
    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */
    'url' => env('APP_URL', 'http://127.0.0.1'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions.
    |
    */
    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */
    'locale' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */
    'key' => env('APP_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Cipher
    |--------------------------------------------------------------------------
    |
    | This option controls the default cipher that will be used for encrypting
    | values. The default is AES-256-CBC which is a secure cipher.
    |
    */
    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode
    |--------------------------------------------------------------------------
    |
    | Here you may configure the maintenance mode settings for your application.
    | You can control when maintenance mode is enabled and what message is
    | displayed to users.
    |
    */
    'maintenance' => [
        'enabled' => env('APP_MAINTENANCE', false),
        'message' => env('APP_MAINTENANCE_MESSAGE', 'Application is in maintenance mode.'),
        'retry' => env('APP_MAINTENANCE_RETRY', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */
    'providers' => [
        // Core Service Providers
        \Core\Providers\ConfigServiceProvider::class,
        \Core\Providers\EventServiceProvider::class,
        \Core\Providers\RouteServiceProvider::class,
        \Core\Providers\ViewServiceProvider::class,
        \Core\Providers\DatabaseServiceProvider::class,
        \Core\Providers\SessionServiceProvider::class,
        \Core\Providers\CacheServiceProvider::class,
        \Core\Providers\LogServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */
    'aliases' => [
        'App' => \Core\Application::class,
        'Config' => \Core\Config::class,
        'DB' => \Core\Database::class,
        'View' => \Core\View\AdvancedView::class,
        'Route' => \Core\Routing\AdvancedRouter::class,
        'Auth' => \Core\Auth\Auth::class,
        'Session' => \Core\Session::class,
    ],
];
