<?php

namespace Core\Middleware;

use Core\Middleware\MiddlewareInterface;
use Core\Session;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle($request, $response = null)
    {
        if (!Session::has('user_id')) {
            if ($request->isXmlHttpRequest()) {
                return new Response(json_encode(['error' => 'Unauthorized']), 401, ['Content-Type' => 'application/json']);
            } else {
                header('Location: /login');
                exit;
            }
        }
    }
}
