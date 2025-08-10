<?php

namespace Core\Routing\Interfaces;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface RouterInterface
{
    /**
     * Add a GET route
     */
    public function get(string $uri, $handler): self;

    /**
     * Add a POST route
     */
    public function post(string $uri, $handler): self;

    /**
     * Add a PUT route
     */
    public function put(string $uri, $handler): self;

    /**
     * Add a DELETE route
     */
    public function delete(string $uri, $handler): self;

    /**
     * Add a PATCH route
     */
    public function patch(string $uri, $handler): self;

    /**
     * Add a route for any HTTP method
     */
    public function any(string $uri, $handler): self;

    /**
     * Add a route with specific HTTP methods
     */
    public function match(array $methods, string $uri, $handler): self;

    /**
     * Group routes
     */
    public function group(array $attributes, callable $callback): self;

    /**
     * Add middleware to routes
     */
    public function middleware($middleware): self;

    /**
     * Add route constraints
     */
    public function where(string $parameter, string $pattern): self;

    /**
     * Add multiple route constraints
     */
    public function whereArray(array $constraints): self;

    /**
     * Add a route
     */
    public function addRoute($method, string $uri, $handler, array $options = []): self;

    /**
     * Dispatch a request
     */
    public function dispatch(Request $request): Response;

    /**
     * Handle 404 Not Found
     */
    public function handleNotFound(Request $request): Response;

    /**
     * Handle 405 Method Not Allowed
     */
    public function handleMethodNotAllowed(Request $request, array $allowedMethods): Response;

    /**
     * Handle found route
     */
    public function handleFound(Request $request, array $routeInfo): Response;

    /**
     * Execute a route
     */
    public function executeRoute(Request $request, array $routeInfo): Response;

    /**
     * Execute a controller action
     */
    public function executeControllerAction(Request $request, string $controller, string $action, array $parameters = []): Response;

    /**
     * Get all routes
     */
    public function getRoutes(): array;

    /**
     * Get a specific route
     */
    public function getRoute(string $name): ?array;

    /**
     * Generate URL for a route
     */
    public function url(string $name, array $parameters = []): string;

    /**
     * Clear all routes
     */
    public function clearRoutes(): void;
}
