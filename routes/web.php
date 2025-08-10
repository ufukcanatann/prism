<?php

/**
 * Web Routes
 * 
 * Register your web routes here. These routes are loaded by the RouteServiceProvider
 * and will be assigned to the "web" middleware group.
 */

// Router instance'ını al
$router = app()->getRouter();

// Basic routes
$router->get('/', function() {
    return view('welcome');
});

// Add your routes here
// Example:
// $router->get('/users', [UserController::class, 'index']);
// $router->post('/users', [UserController::class, 'store']);

// Route groups example:
// $router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function($router) {
//     $router->get('/dashboard', [AdminController::class, 'dashboard']);
// });
