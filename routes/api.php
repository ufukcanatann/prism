<?php

/**
 * API Routes
 * 
 * Register your API routes here. These routes are loaded by the RouteServiceProvider
 * and will be assigned to the "api" middleware group.
 */

// Router instance'ını al
$router = app()->getRouter();

// API routes with prefix
$router->group(['prefix' => '/api'], function($router) {
    
    // Health check endpoint
    $router->get('/health', function() {
        return [
            'status' => 'ok',
            'framework' => 'PRISM',
            'version' => '2.0.0',
            'timestamp' => time()
        ];
    });
    
    // Add your API endpoints here
    // Example:
    // $router->get('/users', [UserController::class, 'index']);
    // $router->post('/users', [UserController::class, 'store']);
    // $router->get('/users/{id}', [UserController::class, 'show']);
    // $router->put('/users/{id}', [UserController::class, 'update']);
    // $router->delete('/users/{id}', [UserController::class, 'destroy']);
    
    // API versioning example:
    // $router->group(['prefix' => '/v1'], function($router) {
    //     $router->get('/posts', [PostController::class, 'index']);
    // });
    
});
