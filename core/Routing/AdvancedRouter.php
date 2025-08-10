<?php

namespace Core\Routing;

use Core\Container\Container;
use Core\Routing\Interfaces\RouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;
use FastRoute\DataGenerator;
use FastRoute\Dispatcher\GroupCountBased;

class AdvancedRouter implements RouterInterface
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var RouteCollector
     */
    protected RouteCollector $routeCollector;

    /**
     * @var Dispatcher
     */
    protected Dispatcher $dispatcher;

    /**
     * @var array
     */
    protected array $routes = [];

    /**
     * @var array
     */
    protected array $groups = [];

    /**
     * @var array
     */
    protected array $middleware = [];

    /**
     * @var array
     */
    protected array $constraints = [];

    /**
     * @var string
     */
    protected string $currentGroup = '';

    /**
     * @var AdvancedRouter|null
     */
    protected static ?AdvancedRouter $instance = null;

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->routeCollector = new RouteCollector(
            new RouteParser\Std(),
            new DataGenerator\GroupCountBased()
        );
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(Container $container): AdvancedRouter
    {
        if (self::$instance === null) {
            self::$instance = new self($container);
        }
        return self::$instance;
    }

    /**
     * Add a GET route
     */
    public function get(string $uri, $handler): self
    {
        $this->addRoute(['GET'], $uri, $handler);
        return $this;
    }

    /**
     * Add a POST route
     */
    public function post(string $uri, $handler): self
    {
        $this->addRoute(['POST'], $uri, $handler);
        return $this;
    }

    /**
     * Add a PUT route
     */
    public function put(string $uri, $handler): self
    {
        $this->addRoute(['PUT'], $uri, $handler);
        return $this;
    }

    /**
     * Add a DELETE route
     */
    public function delete(string $uri, $handler): self
    {
        $this->addRoute(['DELETE'], $uri, $handler);
        return $this;
    }

    /**
     * Add a PATCH route
     */
    public function patch(string $uri, $handler): self
    {
        $this->addRoute(['PATCH'], $uri, $handler);
        return $this;
    }

    /**
     * Add a route for any HTTP method
     */
    public function any(string $uri, $handler): self
    {
        $this->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $uri, $handler);
        return $this;
    }

    /**
     * Add a route with specific HTTP methods
     */
    public function match(array $methods, string $uri, $handler): self
    {
        $this->addRoute($methods, $uri, $handler);
        return $this;
    }

    /**
     * Group routes
     */
    public function group(array $attributes, callable $callback): self
    {
        $previousGroup = $this->currentGroup;
        $this->currentGroup = $attributes['prefix'] ?? '';
        
        $callback($this);
        
        $this->currentGroup = $previousGroup;
        return $this;
    }

    /**
     * Add middleware to routes
     */
    public function middleware($middleware): self
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }
        return $this;
    }

    /**
     * Add route constraints
     */
    public function where(string $parameter, string $pattern): self
    {
        $this->constraints[$parameter] = $pattern;
        return $this;
    }

    /**
     * Add multiple route constraints
     */
    public function whereArray(array $constraints): self
    {
        $this->constraints = array_merge($this->constraints, $constraints);
        return $this;
    }

    /**
     * Add a route
     */
    public function addRoute($method, string $uri, $handler, array $options = []): self
    {
        $uri = $this->currentGroup . $uri;
        
        $this->routes[] = [
            'method' => is_array($method) ? $method : [$method],
            'uri' => $uri,
            'handler' => $handler,
            'options' => $options,
            'middleware' => $this->middleware,
            'constraints' => $this->constraints
        ];
        
        return $this;
    }

    /**
     * Dispatch a request
     */
    public function dispatch(Request $request): Response
    {
        $this->buildDispatcher();
        
        $routeInfo = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getPathInfo()
        );
        
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                return $this->handleNotFound($request);
                
            case Dispatcher::METHOD_NOT_ALLOWED:
                return $this->handleMethodNotAllowed($request, $routeInfo[1]);
                
            case Dispatcher::FOUND:
                return $this->handleFound($request, $routeInfo);
                
            default:
                return new Response('Internal Server Error', 500);
        }
    }

    /**
     * Handle 404 Not Found
     */
    public function handleNotFound(Request $request): Response
    {
        return new Response('404 Not Found', 404);
    }

    /**
     * Handle 405 Method Not Allowed
     */
    public function handleMethodNotAllowed(Request $request, array $allowedMethods): Response
    {
        return new Response('405 Method Not Allowed', 405, [
            'Allow' => implode(', ', $allowedMethods)
        ]);
    }

    /**
     * Handle found route
     */
    public function handleFound(Request $request, array $routeInfo): Response
    {
        $handler = $routeInfo[1];
        $parameters = $routeInfo[2];
        
        return $this->executeRoute($request, $routeInfo);
    }

    /**
     * Execute a route
     */
    public function executeRoute(Request $request, array $routeInfo): Response
    {
        $handler = $routeInfo[1];
        $parameters = $routeInfo[2];
        
        if (is_callable($handler)) {
            $result = call_user_func_array($handler, [$request, $parameters]);
            
            // If result is not a Response object, wrap it in one
            if (!$result instanceof Response) {
                return new Response($result);
            }
            
            return $result;
        }
        
        if (is_array($handler)) {
            return $this->executeControllerAction($request, $handler[0], $handler[1], $parameters);
        }
        
        if (is_string($handler)) {
            return $this->executeControllerAction($request, $handler, 'index', $parameters);
        }
        
        return new Response('Invalid route handler', 500);
    }

    /**
     * Execute a controller action
     */
    public function executeControllerAction(Request $request, string $controller, string $action, array $parameters = []): Response
    {
        $controllerInstance = $this->container->make($controller);
        
        if (!method_exists($controllerInstance, $action)) {
            throw new \Exception("Method {$action} not found in controller {$controller}");
        }
        
        $result = call_user_func_array([$controllerInstance, $action], [$request, $parameters]);
        
        // If result is not a Response object, wrap it in one
        if (!$result instanceof Response) {
            return new Response($result);
        }
        
        return $result;
    }

    /**
     * Get all routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get a specific route
     */
    public function getRoute(string $name): ?array
    {
        foreach ($this->routes as $route) {
            if (isset($route['options']['name']) && $route['options']['name'] === $name) {
                return $route;
            }
        }
        return null;
    }

    /**
     * Generate URL for a route
     */
    public function url(string $name, array $parameters = []): string
    {
        $route = $this->getRoute($name);
        
        if (!$route) {
            throw new \Exception("Route {$name} not found");
        }
        
        $uri = $route['uri'];
        
        foreach ($parameters as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }
        
        return $uri;
    }

    /**
     * Clear all routes
     */
    public function clearRoutes(): void
    {
        $this->routes = [];
        $this->groups = [];
        $this->middleware = [];
        $this->constraints = [];
    }

    /**
     * Build the dispatcher
     */
    protected function buildDispatcher(): void
    {
        $routeCollector = new RouteCollector(
            new RouteParser\Std(),
            new DataGenerator\GroupCountBased()
        );
        
        foreach ($this->routes as $route) {
            $routeCollector->addRoute($route['method'], $route['uri'], $route['handler']);
        }
        
        $this->dispatcher = new GroupCountBased($routeCollector->getData());
    }
}
