<?php

/**
 * Rate Limiting Example
 * 
 * This file shows how to implement rate limiting in your application.
 */

// Example 1: Basic rate limiting for all routes
$router->group(['middleware' => 'rate-limit:60,1'], function() use ($router) {
    $router->get('/api/data', 'ApiController@data');
});

// Example 2: Strict rate limiting for authentication routes
$router->group(['middleware' => 'rate-limit:5,15'], function() use ($router) {
    $router->post('/login', 'AuthController@login');
    $router->post('/register', 'AuthController@register');
    $router->post('/forgot-password', 'AuthController@forgotPassword');
});

// Example 3: Custom rate limiter in controller
/*
class ApiController extends Controller
{
    public function data(Request $request)
    {
        // Custom rate limiting logic
        $rateLimiter = new \Core\Middleware\RateLimitMiddleware(100, 1); // 100 requests per minute
        
        // Apply rate limiting
        $response = $rateLimiter->handle($request, function($req) {
            return $this->getData();
        });
        
        if ($response->getStatusCode() === 429) {
            return $response; // Rate limit exceeded
        }
        
        return $this->jsonResponse([
            'data' => $this->getData()
        ]);
    }
}
*/

// Example 4: Rate limiting with Redis (when available)
/*
// In your middleware configuration
$app->singleton('rate-limiter', function() {
    return new \Core\RateLimit\RedisRateLimiter(
        new Redis(['host' => '127.0.0.1', 'port' => 6379])
    );
});
*/
