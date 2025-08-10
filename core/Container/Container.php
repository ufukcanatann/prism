<?php

namespace Core\Container;

use Core\Container\Exceptions\ContainerException;
use Core\Container\Exceptions\NotFoundException;
use Core\Container\Interfaces\ContainerInterface;
use Core\Container\Interfaces\ServiceProviderInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use ReflectionException;

class Container implements ContainerInterface, PsrContainerInterface
{
    /**
     * @var array
     */
    protected array $bindings = [];

    /**
     * @var array
     */
    protected array $singletons = [];

    /**
     * @var array
     */
    protected array $aliases = [];

    /**
     * @var array
     */
    protected array $resolved = [];

    /**
     * @var array
     */
    protected array $providers = [];

    /**
     * @var array
     */
    protected array $tags = [];

    /**
     * @var Container|null
     */
    protected static ?Container $instance = null;

    /**
     * Get container instance (Singleton pattern)
     */
    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Bind a service to the container
     */
    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        // If concrete is already an instance, store it directly
        if (is_object($concrete) && !$concrete instanceof \Closure) {
            $this->singletons[$abstract] = $concrete;
            return;
        }

        if (!$concrete instanceof \Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Bind a singleton to the container
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Bind an instance to the container
     */
    public function instance(string $abstract, $instance): void
    {
        $this->singletons[$abstract] = $instance;
    }

    /**
     * Register a service provider
     */
    public function register(ServiceProviderInterface $provider): void
    {
        $provider->register($this);
        $this->providers[] = $provider;
    }

    /**
     * Boot all registered service providers
     */
    public function boot(): void
    {
        foreach ($this->providers as $provider) {
            if (method_exists($provider, 'boot')) {
                $provider->boot($this);
            }
        }
    }

    /**
     * Resolve a service from the container
     */
    public function make(string $abstract, array $parameters = [])
    {
        // Check if already resolved
        if (isset($this->resolved[$abstract])) {
            return $this->resolved[$abstract];
        }

        // Check if singleton exists
        if (isset($this->singletons[$abstract])) {
            return $this->singletons[$abstract];
        }

        // Check if binding exists
        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract]['concrete'];
            $shared = $this->bindings[$abstract]['shared'];

            $object = $concrete($this, $parameters);

            if ($shared) {
                $this->resolved[$abstract] = $object;
            }

            return $object;
        }

        // Auto-resolve if not bound
        return $this->resolve($abstract, $parameters);
    }

    /**
     * Auto-resolve a class
     */
    protected function resolve(string $abstract, array $parameters = [])
    {
        try {
            $reflector = new ReflectionClass($abstract);

            if (!$reflector->isInstantiable()) {
                throw new ContainerException("Class {$abstract} is not instantiable");
            }

            $constructor = $reflector->getConstructor();

            if (is_null($constructor)) {
                return new $abstract;
            }

            $dependencies = $this->resolveDependencies($constructor->getParameters(), $parameters);

            return $reflector->newInstanceArgs($dependencies);

        } catch (ReflectionException $e) {
            throw new ContainerException("Could not resolve {$abstract}: " . $e->getMessage());
        }
    }

    /**
     * Resolve dependencies for a method
     */
    protected function resolveDependencies(array $dependencies, array $parameters = []): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            // If parameter is provided, use it
            if (isset($parameters[$dependency->getName()])) {
                $results[] = $parameters[$dependency->getName()];
                continue;
            }

            // If dependency has a default value, use it
            if ($dependency->isDefaultValueAvailable()) {
                $results[] = $dependency->getDefaultValue();
                continue;
            }

            // If dependency is a class, resolve it
            if ($dependency->getClass()) {
                $results[] = $this->make($dependency->getClass()->getName());
                continue;
            }

            // If dependency is not optional, throw exception
            if (!$dependency->isOptional()) {
                throw new ContainerException("Unresolvable dependency: {$dependency->getName()}");
            }
        }

        return $results;
    }

    /**
     * Get closure for binding
     */
    protected function getClosure(string $abstract, string $concrete): \Closure
    {
        return function ($container, $parameters = []) use ($abstract, $concrete) {
            return $container->make($concrete, $parameters);
        };
    }

    /**
     * Check if service is bound
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->singletons[$abstract]);
    }

    /**
     * Check if service is resolved
     */
    public function resolved(string $abstract): bool
    {
        return isset($this->resolved[$abstract]) || isset($this->singletons[$abstract]);
    }

    /**
     * Get all bindings
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Get all resolved instances
     */
    public function getResolved(): array
    {
        return $this->resolved;
    }

    /**
     * Clear resolved instances
     */
    public function clearResolved(): void
    {
        $this->resolved = [];
    }

    /**
     * Flush all bindings and resolved instances
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->singletons = [];
        $this->resolved = [];
        $this->providers = [];
    }

    /**
     * PSR-11 Container Interface implementation
     */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException("Service {$id} not found");
        }

        return $this->make($id);
    }

    /**
     * PSR-11 Container Interface implementation
     */
    public function has(string $id): bool
    {
        return $this->bound($id) || class_exists($id);
    }

    /**
     * Call a method on a resolved instance
     */
    public function call($callback, array $parameters = [])
    {
        if (is_string($callback)) {
            $callback = $this->make($callback);
        }

        if (is_array($callback)) {
            $callback = [$this->make($callback[0]), $callback[1]];
        }

        if (is_callable($callback)) {
            return call_user_func_array($callback, $parameters);
        }

        throw new ContainerException("Invalid callback provided");
    }

    /**
     * Tag a service
     */
    public function tag(string $abstract, array $tags): void
    {
        foreach ($tags as $tag) {
            if (!isset($this->tags[$tag])) {
                $this->tags[$tag] = [];
            }
            $this->tags[$tag][] = $abstract;
        }
    }

    /**
     * Get tagged services
     */
    public function tagged(string $tag): array
    {
        if (!isset($this->tags[$tag])) {
            return [];
        }

        $services = [];
        foreach ($this->tags[$tag] as $abstract) {
            $services[] = $this->make($abstract);
        }

        return $services;
    }
}
