<?php

namespace Core\Providers;

use Core\Container\Container;
use Core\Providers\ServiceProvider;
use Core\Routing\AdvancedRouter;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(Container $container): void
    {
        $container->singleton(AdvancedRouter::class, function (Container $container) {
            return AdvancedRouter::getInstance($container);
        });
    }
}
