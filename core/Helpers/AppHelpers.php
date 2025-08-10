<?php

namespace Core\Helpers;

use Core\Application;
use Core\Container\Container;
use Core\Config;

/**
 * Application Level Helpers
 */
class AppHelpers
{
    /**
     * Get application instance
     */
    public static function app(): Application
    {
        return Application::getInstance();
    }

    /**
     * Get container instance
     */
    public static function container(): Container
    {
        return Container::getInstance();
    }

    /**
     * Resolve a class from the container
     */
    public static function resolve(string $abstract)
    {
        return self::container()->make($abstract);
    }

    /**
     * Get configuration value
     */
    public static function config(string $key, $default = null)
    {
        return Config::get($key, $default);
    }

    /**
     * Set configuration value
     */
    public static function config_set(string $key, $value): void
    {
        Config::set($key, $value);
    }

    /**
     * Get environment variable
     */
    public static function env_custom(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }

    /**
     * Get application URL
     */
    public static function url(string $path = ''): string
    {
        return self::config('app.url') . '/' . ltrim($path, '/');
    }

    /**
     * Get asset URL
     */
    public static function asset(string $path): string
    {
        return self::url('assets/' . ltrim($path, '/'));
    }

    /**
     * Get route URL
     */
    public static function route(string $name, array $parameters = []): string
    {
        return self::container()->make('router')->url($name, $parameters);
    }

    /**
     * Get current URL
     */
    public static function current_url(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Get previous URL
     */
    public static function previous_url(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? self::url();
    }

    /**
     * Redirect to URL
     */
    public static function redirect(string $url, int $status = 302): void
    {
        header('Location: ' . $url, true, $status);
        exit;
    }

    /**
     * Redirect back
     */
    public static function back(): void
    {
        $url = self::previous_url();
        if (empty($url)) {
            $url = self::url();
        }
        self::redirect($url);
    }

    /**
     * Redirect to route
     */
    public static function redirect_route(string $name, array $parameters = []): void
    {
        self::redirect(self::route($name, $parameters));
    }
}
