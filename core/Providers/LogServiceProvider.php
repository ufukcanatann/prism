<?php

namespace Core\Providers;

use Core\Container\Container;
use Core\Providers\ServiceProvider;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(Container $container): void
    {
        $container->singleton(LoggerInterface::class, function (Container $container) {
            $logger = new Logger('app');
            
            // Console handler for development
            if ($container->make('config')->get('app.debug', false)) {
                $consoleHandler = new StreamHandler('php://stdout', Logger::DEBUG);
                $consoleHandler->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"));
                $logger->pushHandler($consoleHandler);
            }
            
            // File handler for production
            $logPath = $container->make('config')->get('app.log_path', __DIR__ . '/../../storage/logs/app.log');
            $fileHandler = new RotatingFileHandler($logPath, 30, Logger::INFO);
            $fileHandler->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"));
            $logger->pushHandler($fileHandler);
            
            return $logger;
        });
        
        // Bind Monolog Logger as well
        $container->singleton(Logger::class, function (Container $container) {
            return $container->make(LoggerInterface::class);
        });
    }
}
