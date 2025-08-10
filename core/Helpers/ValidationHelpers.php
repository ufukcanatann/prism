<?php

namespace Core\Helpers;

class ValidationHelpers
{
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function url(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function ip(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    public static function alpha(string $value): bool
    {
        return ctype_alpha($value);
    }

    public static function alphaNumeric(string $value): bool
    {
        return ctype_alnum($value);
    }

    public static function numeric($value): bool
    {
        return is_numeric($value);
    }

    public static function integer($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public static function float($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    public static function boolean($value): bool
    {
        return in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true);
    }

    public static function date(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public static function minLength(string $value, int $min): bool
    {
        return mb_strlen($value) >= $min;
    }

    public static function maxLength(string $value, int $max): bool
    {
        return mb_strlen($value) <= $max;
    }

    public static function length(string $value, int $length): bool
    {
        return mb_strlen($value) === $length;
    }

    public static function between($value, $min, $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    public static function in($value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    public static function notIn($value, array $forbidden): bool
    {
        return !in_array($value, $forbidden, true);
    }

    public static function unique(string $table, string $column, $value, $ignore = null): bool
    {
        // Bu fonksiyon veritabanı bağlantısı gerektirir
        // Şimdilik basit bir implementasyon
        return true;
    }

    public static function exists(string $table, string $column, $value): bool
    {
        // Bu fonksiyon veritabanı bağlantısı gerektirir
        // Şimdilik basit bir implementasyon
        return true;
    }
}
