<?php

namespace Core\Helpers;

class ArrayHelpers
{
    public static function get(array $array, string $key, $default = null)
    {
        return $array[$key] ?? $default;
    }

    public static function has(array $array, string $key): bool
    {
        return array_key_exists($key, $array);
    }

    public static function first(array $array)
    {
        return reset($array);
    }

    public static function last(array $array)
    {
        return end($array);
    }

    public static function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    public static function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    public static function pull(array &$array, string $key, $default = null)
    {
        $value = $array[$key] ?? $default;
        unset($array[$key]);
        return $value;
    }

    public static function add(array $array, string $key, $value): array
    {
        if (!array_key_exists($key, $array)) {
            $array[$key] = $value;
        }
        return $array;
    }

    public static function set(array &$array, string $key, $value): array
    {
        $array[$key] = $value;
        return $array;
    }

    public static function forget(array &$array, string $key): array
    {
        unset($array[$key]);
        return $array;
    }

    public static function wrap($value): array
    {
        if (is_null($value)) {
            return [];
        }
        return is_array($value) ? $value : [$value];
    }

    public static function flatten(array $array, int $depth = INF): array
    {
        $result = [];
        foreach ($array as $item) {
            if (is_array($item) && $depth > 0) {
                $result = array_merge($result, self::flatten($item, $depth - 1));
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }

    public static function pluck(array $array, string $value, string $key = null): array
    {
        $results = [];
        foreach ($array as $item) {
            $itemValue = is_object($item) ? $item->{$value} : $item[$value];
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = is_object($item) ? $item->{$key} : $item[$key];
                $results[$itemKey] = $itemValue;
            }
        }
        return $results;
    }

    public static function where(array $array, callable $callback): array
    {
        return array_filter($array, $callback);
    }

    public static function mapAssoc(array $array, callable $callback): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[$key] = $callback($value, $key);
        }
        return $result;
    }
}
