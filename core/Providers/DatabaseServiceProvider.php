<?php

namespace Core\Providers;

use Core\Container\Container;
use Core\Providers\ServiceProvider;
use PDO;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(Container $container): void
    {
        $container->singleton(PDO::class, function (Container $container) {
            $config = $container->make('config');
            $dsn = "mysql:host={$config->get('database.host')};dbname={$config->get('database.name')};charset=utf8mb4";
            
            $pdo = new PDO($dsn, $config->get('database.username'), $config->get('database.password'), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            
            return $pdo;
        });
    }
}
