<?php

namespace Core\Providers;

use Core\Container\Container;
use Core\Providers\ServiceProvider;
use Core\Config;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(Container $container): void
    {
        $container->singleton(Config::class, function (Container $container) {
            return new Config();
        });
        
        // Bind 'config' alias to Config class
        $container->singleton('config', function (Container $container) {
            return $container->make(Config::class);
        });
    }
}
