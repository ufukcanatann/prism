<?php

namespace Core;

use Dotenv\Dotenv;

class Config
{
    private static $config = [];

    public static function load()
    {
        // .env dosyasını yükle
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        }

        // Temel konfigürasyon
        self::$config = [
            'app' => [
                'name' => $_ENV['APP_NAME'] ?? 'Kozuva',
                'env' => $_ENV['APP_ENV'] ?? 'local',
                'debug' => $_ENV['APP_DEBUG'] ?? true,
                'url' => $_ENV['APP_URL'] ?? 'http://127.0.0.1:8000',
            ],
            'database' => [
                'connection' => $_ENV['DB_CONNECTION'] ?? 'mysql',
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'database' => $_ENV['DB_DATABASE'] ?? 'new_dys',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
            ],
            'session' => [
                'driver' => $_ENV['SESSION_DRIVER'] ?? 'file',
                'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 120,
            ],
            'cache' => [
                'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
            ],
            'mail' => [
                'driver' => $_ENV['MAIL_MAILER'] ?? 'smtp',
                'host' => $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io',
                'port' => $_ENV['MAIL_PORT'] ?? '2525',
                'username' => $_ENV['MAIL_USERNAME'] ?? null,
                'password' => $_ENV['MAIL_PASSWORD'] ?? null,
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? null,
                'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? null,
                'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Kozuva',
            ],
            'middleware' => [
                'before' => [
                    \Core\Middleware\CorsMiddleware::class,
                    \Core\Middleware\SessionMiddleware::class,
                ],
                'after' => [
                    \Core\Middleware\ResponseMiddleware::class,
                ]
            ]
        ];
    }

    public static function get($key, $default = null)
    {
        if (empty(self::$config)) {
            self::load();
        }

        $keys = explode('.', $key);
        $config = self::$config;

        foreach ($keys as $segment) {
            if (isset($config[$segment])) {
                $config = $config[$segment];
            } else {
                return $default;
            }
        }

        return $config;
    }

    public static function set($key, $value)
    {
        if (empty(self::$config)) {
            self::load();
        }

        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }

        $config = $value;
    }
}
