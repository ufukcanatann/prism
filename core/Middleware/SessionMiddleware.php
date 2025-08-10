<?php

namespace Core\Middleware;

use Core\Middleware\MiddlewareInterface;
use Core\Session;

class SessionMiddleware implements MiddlewareInterface
{
    public function handle($request, $response = null)
    {
        Session::start();
    }
}
