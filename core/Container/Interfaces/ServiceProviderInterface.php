<?php

namespace Core\Container\Interfaces;

use Core\Container\Container;

interface ServiceProviderInterface
{
    /**
     * Register services to the container
     */
    public function register(Container $container): void;

    /**
     * Boot the service provider
     */
    public function boot(Container $container): void;
}
