<?php

namespace Core;

class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove($key)
    {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy()
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    public static function regenerate()
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function flash($key, $value = null)
    {
        self::start();
        if ($value !== null) {
            $_SESSION['flash'][$key] = $value;
        } else {
            $value = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $value;
        }
    }

    public static function hasFlash($key)
    {
        self::start();
        return isset($_SESSION['flash'][$key]);
    }

    public static function all()
    {
        self::start();
        return $_SESSION;
    }

    public static function id()
    {
        self::start();
        return session_id();
    }
}
