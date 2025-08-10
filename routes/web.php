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

// Version info route
$router->get('/version', function() {
    return [
        'framework' => 'PRISM',
        'version' => config('app.version'),
        'php_version' => PHP_VERSION
    ];
});

// APP_KEY generate endpoint
$router->post('/generate-app-key', function() {
    try {
        // AppKeyGenerator sınıfını kullan
        $result = \Core\Helpers\AppKeyGenerator::generateAndSave();
        
        // JSON response döndür
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
        
    } catch (\Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Hata: ' . $e->getMessage()
        ]);
        exit;
    }
});


// Add your routes here
// Example:
// $router->get('/users', [UserController::class, 'index']);
// $router->post('/users', [UserController::class, 'store']);

// Route groups example:
// $router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function($router) {
//     $router->get('/dashboard', [AdminController::class, 'dashboard']);
// });
