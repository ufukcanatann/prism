<?php

namespace Core\Middleware;

use Core\Middleware\MiddlewareInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function handle($request, $response = null)
    {
        if ($response) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }
    }
}
