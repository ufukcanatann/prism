<?php

namespace Core\View\Helpers;

use Core\Container\Container;

class BladeHelpers
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var array
     */
    protected array $helpers = [];

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->registerDefaultHelpers();
    }

    /**
     * Register default helpers
     */
    protected function registerDefaultHelpers(): void
    {
        // Auth helpers
        $this->register('auth', function() {
            return $this->container->make('auth');
        });

        $this->register('user', function() {
            return auth()->user();
        });

        $this->register('check', function() {
            return auth()->check();
        });

        $this->register('guest', function() {
            return !auth()->check();
        });

        // URL helpers
        $this->register('url', function($path = '') {
            return $this->container->make('config')->get('app.url') . '/' . ltrim($path, '/');
        });

        $this->register('asset', function($path) {
            return $this->container->make('config')->get('app.url') . '/assets/' . ltrim($path, '/');
        });

        $this->register('route', function($name, $parameters = []) {
            return $this->container->make('router')->url($name, $parameters);
        });

        // Form helpers
        $this->register('csrf_field', function() {
            return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
        });

        $this->register('method_field', function($method) {
            return '<input type="hidden" name="_method" value="' . $method . '">';
        });

        $this->register('old', function($key, $default = '') {
            return $this->container->make('session')->get('_old_input.' . $key, $default);
        });

        // Session helpers
        $this->register('session', function($key, $default = null) {
            return $this->container->make('session')->get($key, $default);
        });

        $this->register('has_session', function($key) {
            return $this->container->make('session')->has($key);
        });

        // Flash helpers
        $this->register('flash', function($key, $default = '') {
            return $this->container->make('session')->getFlash($key, $default);
        });

        $this->register('has_flash', function($key) {
            return $this->container->make('session')->hasFlash($key);
        });

        // Config helpers
        $this->register('config', function($key, $default = null) {
            return $this->container->make('config')->get($key, $default);
        });

        $this->register('env', function($key, $default = null) {
            return env_custom($key, $default);
        });

        // Date helpers
        $this->register('now', function($format = 'Y-m-d H:i:s') {
            return date($format);
        });

        $this->register('date', function($date, $format = 'Y-m-d H:i:s') {
            return date($format, strtotime($date));
        });

        // Permission helpers
        $this->register('can', function($permission) {
            if (!auth()->check()) {
                return false;
            }
            return auth()->user()->can($permission);
        });

        $this->register('has_role', function($role) {
            if (!auth()->check()) {
                return false;
            }
            return auth()->user()->hasRole($role);
        });

        $this->register('has_permission', function($permission) {
            if (!auth()->check()) {
                return false;
            }
            return auth()->user()->hasPermission($permission);
        });

        // Validation helpers
        $this->register('error', function($field) {
            return $this->container->make('session')->getFlash('errors.' . $field, '');
        });

        $this->register('has_error', function($field) {
            return $this->container->make('session')->hasFlash('errors.' . $field);
        });

        // Request helpers
        $this->register('request', function($key, $default = null) {
            return $_REQUEST[$key] ?? $default;
        });

        $this->register('has_request', function($key) {
            return isset($_REQUEST[$key]);
        });

        // Cookie helpers
        $this->register('cookie', function($key, $default = null) {
            return $_COOKIE[$key] ?? $default;
        });

        $this->register('has_cookie', function($key) {
            return isset($_COOKIE[$key]);
        });

        // String helpers
        $this->register('e', function($value) {
            return e_custom($value);
        });

        $this->register('str_limit', function($value, $limit = 100, $end = '...') {
            if (strlen($value) <= $limit) {
                return $value;
            }
            return substr($value, 0, $limit) . $end;
        });

        $this->register('str_random', function($length = 16) {
            $string = '';
            while (($len = strlen($string)) < $length) {
                $size = $length - $len;
                $bytes = random_bytes($size);
                $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
            }
            return $string;
        });

        // Array helpers
        $this->register('array_get', function($array, $key, $default = null) {
            return $array[$key] ?? $default;
        });

        $this->register('array_has', function($array, $key) {
            return isset($array[$key]);
        });

        $this->register('array_first', function($array) {
            return reset($array);
        });

        $this->register('array_last', function($array) {
            return end($array);
        });

        // Number helpers
        $this->register('number_format', function($number, $decimals = 0, $dec_point = '.', $thousands_sep = ',') {
            return number_format_custom($number, $decimals, $dec_point, $thousands_sep);
        });

        $this->register('format_bytes', function($bytes, $precision = 2) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                $bytes /= 1024;
            }
            return round($bytes, $precision) . ' ' . $units[$i];
        });

        // File helpers
        $this->register('file_exists', function($path) {
            return file_exists($path);
        });

        $this->register('file_size', function($path) {
            return filesize($path);
        });

        $this->register('file_extension', function($path) {
            return pathinfo($path, PATHINFO_EXTENSION);
        });

        $this->register('file_name', function($path) {
            return pathinfo($path, PATHINFO_FILENAME);
        });

        // Cache helpers
        $this->register('cache_exists', function($key) {
            return $this->container->make('cache')->hasItem($key);
        });

        $this->register('cache_start', function($key) {
            // Start output buffering for cache
            ob_start();
        });

        $this->register('cache_end', function() {
            // End output buffering and store in cache
            $content = ob_get_clean();
            // Store in cache logic here
            return $content;
        });

        // Component helpers
        $this->register('component', function($name, $data = []) {
            return $this->container->make('view')->render('components.' . $name, $data);
        });

        $this->register('slot', function($name = 'default') {
            // Slot logic here
            return '';
        });

        $this->register('end_slot', function() {
            // End slot logic here
        });

        // Form helpers
        $this->register('form_open', function($action = '', $method = 'POST', $attributes = []) {
            $attr = '';
            foreach ($attributes as $key => $value) {
                $attr .= ' ' . $key . '="' . $value . '"';
            }
            return '<form action="' . $action . '" method="' . $method . '"' . $attr . '>';
        });

        $this->register('form_close', function() {
            return '</form>';
        });

        $this->register('form_input', function($data = []) {
            $name = $data['name'] ?? '';
            $value = $data['value'] ?? '';
            $type = $data['type'] ?? 'text';
            $attributes = $data['attributes'] ?? [];
            
            $attr = '';
            foreach ($attributes as $key => $value) {
                $attr .= ' ' . $key . '="' . $value . '"';
            }
            
            return '<input type="' . $type . '" name="' . $name . '" value="' . $value . '"' . $attr . '>';
        });

        $this->register('form_textarea', function($data = []) {
            $name = $data['name'] ?? '';
            $value = $data['value'] ?? '';
            $attributes = $data['attributes'] ?? [];
            
            $attr = '';
            foreach ($attributes as $key => $value) {
                $attr .= ' ' . $key . '="' . $value . '"';
            }
            
            return '<textarea name="' . $name . '"' . $attr . '>' . $value . '</textarea>';
        });

        $this->register('form_select', function($data = []) {
            $name = $data['name'] ?? '';
            $options = $data['options'] ?? [];
            $selected = $data['selected'] ?? '';
            $attributes = $data['attributes'] ?? [];
            
            $attr = '';
            foreach ($attributes as $key => $value) {
                $attr .= ' ' . $key . '="' . $value . '"';
            }
            
            $html = '<select name="' . $name . '"' . $attr . '>';
            foreach ($options as $value => $label) {
                $isSelected = ($value == $selected) ? ' selected' : '';
                $html .= '<option value="' . $value . '"' . $isSelected . '>' . $label . '</option>';
            }
            $html .= '</select>';
            
            return $html;
        });

        $this->register('form_checkbox', function($data = []) {
            $name = $data['name'] ?? '';
            $value = $data['value'] ?? '1';
            $checked = $data['checked'] ?? false;
            $attributes = $data['attributes'] ?? [];
            
            $attr = '';
            foreach ($attributes as $key => $value) {
                $attr .= ' ' . $key . '="' . $value . '"';
            }
            
            $checkedAttr = $checked ? ' checked' : '';
            
            return '<input type="checkbox" name="' . $name . '" value="' . $value . '"' . $checkedAttr . $attr . '>';
        });

        $this->register('form_radio', function($data = []) {
            $name = $data['name'] ?? '';
            $value = $data['value'] ?? '';
            $checked = $data['checked'] ?? false;
            $attributes = $data['attributes'] ?? [];
            
            $attr = '';
            foreach ($attributes as $key => $value) {
                $attr .= ' ' . $key . '="' . $value . '"';
            }
            
            $checkedAttr = $checked ? ' checked' : '';
            
            return '<input type="radio" name="' . $name . '" value="' . $value . '"' . $checkedAttr . $attr . '>';
        });

        $this->register('form_submit', function($data = []) {
            $value = $data['value'] ?? 'Submit';
            $attributes = $data['attributes'] ?? [];
            
            $attr = '';
            foreach ($attributes as $key => $value) {
                $attr .= ' ' . $key . '="' . $value . '"';
            }
            
            return '<input type="submit" value="' . $value . '"' . $attr . '>';
        });

        // Debug helpers
        $this->register('dump', function($var) {
            if ($this->container->make('config')->get('app.debug', false)) {
                dump($var);
            }
        });

        $this->register('dd', function($var) {
            if ($this->container->make('config')->get('app.debug', false)) {
                dd($var);
            }
        });

        // Performance helpers
        $this->register('benchmark', function($callback) {
            $start = microtime(true);
            $result = $callback();
            $end = microtime(true);
            $time = ($end - $start) * 1000;
            
            if ($this->container->make('config')->get('app.debug', false)) {
                echo "<!-- Benchmark: {$time}ms -->";
            }
            
            return $result;
        });

        // Security helpers
        $this->register('csrf_token', function() {
            return $this->container->make('session')->get('_token', '');
        });

        $this->register('hash', function($value) {
            return password_hash($value, PASSWORD_DEFAULT);
        });

        $this->register('verify', function($value, $hash) {
            return password_verify($value, $hash);
        });

        // Utility helpers
        $this->register('is_null', function($value) {
            return is_null($value);
        });

        $this->register('is_empty', function($value) {
            return empty($value);
        });

        $this->register('is_array', function($value) {
            return is_array($value);
        });

        $this->register('is_string', function($value) {
            return is_string($value);
        });

        $this->register('is_numeric', function($value) {
            return is_numeric($value);
        });

        $this->register('is_bool', function($value) {
            return is_bool($value);
        });

        $this->register('is_object', function($value) {
            return is_object($value);
        });

        $this->register('is_callable', function($value) {
            return is_callable($value);
        });

        $this->register('is_file', function($value) {
            return is_file($value);
        });

        $this->register('is_dir', function($value) {
            return is_dir($value);
        });

        $this->register('is_readable', function($value) {
            return is_readable($value);
        });

        $this->register('is_writable', function($value) {
            return is_writable($value);
        });
    }

    /**
     * Register a helper
     */
    public function register(string $name, callable $callback): void
    {
        $this->helpers[$name] = $callback;
    }

    /**
     * Get a helper
     */
    public function get(string $name): ?callable
    {
        return $this->helpers[$name] ?? null;
    }

    /**
     * Get all helpers
     */
    public function all(): array
    {
        return $this->helpers;
    }

    /**
     * Check if helper exists
     */
    public function has(string $name): bool
    {
        return isset($this->helpers[$name]);
    }

    /**
     * Remove a helper
     */
    public function remove(string $name): void
    {
        unset($this->helpers[$name]);
    }

    /**
     * Clear all helpers
     */
    public function clear(): void
    {
        $this->helpers = [];
    }

    /**
     * Call a helper
     */
    public function call(string $name, ...$arguments)
    {
        if ($this->has($name)) {
            return call_user_func_array($this->get($name), $arguments);
        }
        
        throw new \Exception("Helper '{$name}' not found");
    }
}
