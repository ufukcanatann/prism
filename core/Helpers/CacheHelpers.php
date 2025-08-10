<?php

namespace Core\Helpers;

class CacheHelpers
{
    public static function get(string $key, $default = null)
    {
        $cachePath = \storage_path('cache/' . md5($key) . '.cache');
        if (file_exists($cachePath)) {
            $data = unserialize(file_get_contents($cachePath));
            if ($data['expires'] > time()) {
                return $data['value'];
            }
            unlink($cachePath);
        }
        return $default;
    }

    public static function set(string $key, $value, int $ttl = 3600): bool
    {
        $cachePath = \storage_path('cache/' . md5($key) . '.cache');
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        return file_put_contents($cachePath, serialize($data)) !== false;
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    public static function delete(string $key): bool
    {
        $cachePath = \storage_path('cache/' . md5($key) . '.cache');
        return file_exists($cachePath) ? unlink($cachePath) : true;
    }

    public static function clear(): bool
    {
        $cacheDir = \storage_path('cache/');
        $files = glob($cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }

    public static function remember(string $key, int $ttl, callable $callback)
    {
        $value = self::get($key);
        if ($value !== null) {
            return $value;
        }
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    public static function rememberForever(string $key, callable $callback)
    {
        return self::remember($key, 0, $callback);
    }

    public static function increment(string $key, int $value = 1): int
    {
        $current = (int) self::get($key, 0);
        $new = $current + $value;
        self::set($key, $new);
        return $new;
    }

    public static function decrement(string $key, int $value = 1): int
    {
        return self::increment($key, -$value);
    }
}
