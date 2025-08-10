<?php

namespace Core\Providers;

use Core\Container\Container;
use Core\Container\Interfaces\ServiceProviderInterface;

abstract class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var bool
     */
    protected bool $deferred = false;

    /**
     * @var array
     */
    protected array $provides = [];

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register services
     */
    abstract public function register(Container $container): void;

    /**
     * Boot services
     */
    public function boot(Container $container): void
    {
        // Override in child classes
    }

    /**
     * Check if provider is deferred
     */
    public function isDeferred(): bool
    {
        return $this->deferred;
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return $this->provides;
    }

    /**
     * Get the events that trigger this service provider to load
     */
    public function when(): array
    {
        return [];
    }
}
