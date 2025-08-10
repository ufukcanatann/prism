<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    */

    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    */

    'csp' => [
        'default-src' => "'self'",
        'script-src' => "'self' 'unsafe-inline'",
        'style-src' => "'self' 'unsafe-inline'",
        'img-src' => "'self' data: https:",
        'connect-src' => "'self'",
        'font-src' => "'self'",
        'object-src' => "'none'",
        'media-src' => "'self'",
        'frame-src' => "'none'",
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Strict Transport Security
    |--------------------------------------------------------------------------
    */

    'hsts' => [
        'max-age' => 31536000, // 1 year
        'include-subdomains' => true,
        'preload' => false,
    ],
];
