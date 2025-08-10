<?php

namespace Core\Providers;

use Core\Container\Container;
use Core\Providers\ServiceProvider;
use Core\Events\EventDispatcher;
use Core\Events\Interfaces\EventDispatcherInterface;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(Container $container): void
    {
        $container->singleton(EventDispatcherInterface::class, function (Container $container) {
            return EventDispatcher::getInstance($container);
        });
        
        $container->singleton(EventDispatcher::class, function (Container $container) {
            return EventDispatcher::getInstance($container);
        });
    }
}
