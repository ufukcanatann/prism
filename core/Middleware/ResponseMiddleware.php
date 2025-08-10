<?php

namespace Core\Middleware;

use Core\Middleware\MiddlewareInterface;

class ResponseMiddleware implements MiddlewareInterface
{
    public function handle($request, $response = null)
    {
        if ($response) {
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        }
    }
}
