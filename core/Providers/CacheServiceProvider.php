<?php

namespace Core\Providers;

use Core\Container\Container;
use Core\Providers\ServiceProvider;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Psr\Cache\CacheItemPoolInterface;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(Container $container): void
    {
        $container->singleton(CacheItemPoolInterface::class, function (Container $container) {
            return new FilesystemAdapter('app', 3600, __DIR__ . '/../../storage/cache');
        });
        
        $container->singleton(FilesystemAdapter::class, function (Container $container) {
            return $container->make(CacheItemPoolInterface::class);
        });
    }
}
