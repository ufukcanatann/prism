<?php

use Core\Application;
use Core\Container\Container;
use Core\View\AdvancedView;

// Load security helpers
require_once __DIR__ . '/Helpers/SecurityHelpers.php';

// Load database helpers
require_once __DIR__ . '/Helpers/DatabaseHelpers.php';

// Load factory helpers
require_once __DIR__ . '/Helpers/FactoryHelpers.php';

// Global helper fonksiyonları - yeni modüler yapıya göre

/**
 * Application helpers
 */
function app(): Application
{
    return Application::getInstance();
}

function container(): Container
{
    return app()->getContainer();
}

function resolve(string $abstract)
{
    return container()->make($abstract);
}

function config(string $key, $default = null)
{
    return \Core\Config::get($key, $default);
}

function config_set(string $key, $value): void
{
    \Core\Config::set($key, $value);
}

function env_custom(string $key, $default = null)
{
    return $_ENV[$key] ?? $default;
}

function url(string $path = ''): string
{
    $baseUrl = config('app.url', 'http://127.0.0.1');
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    // Public klasöründen çalıştığımız için relative path döndür
    return '/' . ltrim($path, '/');
}

function route(string $name, array $parameters = []): string
{
    return app()->getRouter()->url($name, $parameters);
}

function current_url(): string
{
    $protocol = is_ssl() ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function previous_url(): string
{
    return $_SERVER['HTTP_REFERER'] ?? '/';
}

function redirect(string $url, int $status = 302)
{
    header('Location: ' . $url, true, $status);
    exit;
}

function back()
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    redirect($referer);
}

function redirect_route(string $name, array $parameters = [])
{
    redirect(route($name, $parameters));
}

/**
 * Session helpers
 */
function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . $method . '">';
}

function old(string $key, $default = ''): string
{
    return $_SESSION['old'][$key] ?? $default;
}

function session(string $key, $default = null)
{
    return $_SESSION[$key] ?? $default;
}

function has_session(string $key): bool
{
    return isset($_SESSION[$key]);
}

function session_set(string $key, $value): void
{
    $_SESSION[$key] = $value;
}

function session_remove(string $key): void
{
    unset($_SESSION[$key]);
}

function flash(string $key, $value): void
{
    $_SESSION['flash'][$key] = $value;
}

function flash_get(string $key, $default = ''): string
{
    $value = $_SESSION['flash'][$key] ?? $default;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function has_flash(string $key): bool
{
    return isset($_SESSION['flash'][$key]);
}

function flash_message(string $key, $default = ''): string
{
    return flash_get($key, $default);
}

/**
 * Request helpers
 */
function request(string $key, $default = null)
{
    return $_REQUEST[$key] ?? $default;
}

function has_request(string $key): bool
{
    return isset($_REQUEST[$key]);
}

function post(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}

function has_post(string $key): bool
{
    return isset($_POST[$key]);
}

function get(string $key, $default = null)
{
    return $_GET[$key] ?? $default;
}

function has_get(string $key): bool
{
    return isset($_GET[$key]);
}

/**
 * Cookie helpers
 */
function cookie(string $key, $default = null)
{
    return $_COOKIE[$key] ?? $default;
}

function has_cookie(string $key): bool
{
    return isset($_COOKIE[$key]);
}

function set_cookie(string $name, string $value, int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false): bool
{
    return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
}

function delete_cookie(string $name): bool
{
    return setcookie($name, '', time() - 3600, '/');
}

/**
 * Date helpers
 */
function now(string $format = 'Y-m-d H:i:s'): string
{
    return date($format);
}

function date_format_custom(string $date, string $format = 'Y-m-d H:i:s'): string
{
    return date($format, strtotime($date));
}

function time_custom(): int
{
    return time();
}

function microtime_float(): float
{
    return microtime(true);
}

/**
 * String helpers - yeni StringHelpers sınıfını kullan
 */
function e_custom(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
}

function e_double(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', true);
}

function raw($value): string
{
    return $value;
}

function str_limit(string $value, int $limit = 100, string $end = '...'): string
{
    return \Core\Helpers\StringHelpers::limit($value, $limit, $end);
}

function str_random(int $length = 16): string
{
    $string = '';
    while (($len = strlen($string)) < $length) {
        $size = $length - $len;
        $bytes = random_bytes($size);
        $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
    }
    return $string;
}

function str_lower(string $value): string
{
    return \Core\Helpers\StringHelpers::lower($value);
}

function str_upper(string $value): string
{
    return \Core\Helpers\StringHelpers::upper($value);
}

function str_ucfirst(string $value): string
{
    return \Core\Helpers\StringHelpers::ucfirst($value);
}

function str_ucwords(string $value): string
{
    return \Core\Helpers\StringHelpers::ucwords($value);
}

function str_title(string $value): string
{
    return \Core\Helpers\StringHelpers::title($value);
}

function str_slug(string $value, string $separator = '-'): string
{
    return \Core\Helpers\StringHelpers::slug($value, $separator);
}

function str_camel(string $value): string
{
    return \Core\Helpers\StringHelpers::camel($value);
}

function str_snake(string $value): string
{
    return \Core\Helpers\StringHelpers::snake($value);
}

function str_kebab(string $value): string
{
    return \Core\Helpers\StringHelpers::kebab($value);
}

function str_plural(string $value): string
{
    return \Core\Helpers\StringHelpers::plural($value);
}

function str_singular(string $value): string
{
    return \Core\Helpers\StringHelpers::singular($value);
}

/**
 * Number helpers
 */
function number_format_custom($number, int $decimals = 0, string $dec_point = '.', string $thousands_sep = ','): string
{
    return number_format($number, $decimals, $dec_point, $thousands_sep);
}

function format_bytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

function format_currency($amount, string $currency = 'TRY', int $decimals = 2): string
{
    return number_format($amount, $decimals) . ' ' . $currency;
}

function format_percentage($value, int $decimals = 2): string
{
    return number_format($value, $decimals) . '%';
}

/**
 * Array helpers - yeni ArrayHelpers sınıfını kullan
 */
function array_get(array $array, string $key, $default = null)
{
    return \Core\Helpers\ArrayHelpers::get($array, $key, $default);
}

function array_has(array $array, string $key): bool
{
    return \Core\Helpers\ArrayHelpers::has($array, $key);
}

function array_first(array $array)
{
    return \Core\Helpers\ArrayHelpers::first($array);
}

function array_last(array $array)
{
    return \Core\Helpers\ArrayHelpers::last($array);
}

function array_only(array $array, array $keys): array
{
    return \Core\Helpers\ArrayHelpers::only($array, $keys);
}

function array_except(array $array, array $keys): array
{
    return \Core\Helpers\ArrayHelpers::except($array, $keys);
}

function array_pull(array &$array, string $key, $default = null)
{
    return \Core\Helpers\ArrayHelpers::pull($array, $key, $default);
}

function array_add(array $array, string $key, $value): array
{
    return \Core\Helpers\ArrayHelpers::add($array, $key, $value);
}

function array_set(array &$array, string $key, $value): array
{
    return \Core\Helpers\ArrayHelpers::set($array, $key, $value);
}

function array_forget(array &$array, string $key): array
{
    return \Core\Helpers\ArrayHelpers::forget($array, $key);
}

function array_wrap($value): array
{
    return \Core\Helpers\ArrayHelpers::wrap($value);
}

function array_flatten(array $array, int $depth = INF): array
{
    return \Core\Helpers\ArrayHelpers::flatten($array, $depth);
}

function array_pluck(array $array, string $value, string $key = null): array
{
    return \Core\Helpers\ArrayHelpers::pluck($array, $value, $key);
}

function array_where(array $array, callable $callback): array
{
    return \Core\Helpers\ArrayHelpers::where($array, $callback);
}

function array_map_assoc(array $array, callable $callback): array
{
    return \Core\Helpers\ArrayHelpers::mapAssoc($array, $callback);
}

/**
 * Collection helper
 */
if (!function_exists('collect_custom')) {
    function collect_custom($items = []): \Core\Support\Collection
    {
        return new \Core\Support\Collection($items);
    }
}

/**
 * String helpers - PHP 8+ fonksiyonları için kontrol
 */
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return \Core\Helpers\StringHelpers::contains($haystack, $needle);
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return \Core\Helpers\StringHelpers::starts_with($haystack, $needle);
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return \Core\Helpers\StringHelpers::ends_with($haystack, $needle);
    }
}

function str_before(string $subject, string $search): string
{
    return \Core\Helpers\StringHelpers::before($subject, $search);
}

function str_after(string $subject, string $search): string
{
    return \Core\Helpers\StringHelpers::after($subject, $search);
}

function str_between(string $subject, string $from, string $to): string
{
    return \Core\Helpers\StringHelpers::between($subject, $from, $to);
}

function str_replace_first(string $search, string $replace, string $subject): string
{
    return \Core\Helpers\StringHelpers::replace_first($search, $replace, $subject);
}

function str_replace_last(string $search, string $replace, string $subject): string
{
    return \Core\Helpers\StringHelpers::replace_last($search, $replace, $subject);
}

function str_random_words(int $count = 3): string
{
    return \Core\Helpers\StringHelpers::random_words($count);
}

function str_random_sentence(int $wordCount = 5): string
{
    return \Core\Helpers\StringHelpers::random_sentence($wordCount);
}

function str_random_paragraph(int $sentenceCount = 3): string
{
    return \Core\Helpers\StringHelpers::random_paragraph($sentenceCount);
}

/**
 * File helpers - yeni FileHelpers sınıfını kullan
 */
if (!function_exists('file_exists_custom')) {
    function file_exists_custom($path): bool
    {
        return \Core\Helpers\FileHelpers::exists($path);
    }
}

function file_size($path): int
{
    return \Core\Helpers\FileHelpers::size($path);
}

function file_extension($path): string
{
    return \Core\Helpers\FileHelpers::extension($path);
}

function file_name($path): string
{
    return \Core\Helpers\FileHelpers::name($path);
}

function file_basename($path): string
{
    return \Core\Helpers\FileHelpers::basename($path);
}

function file_dirname($path): string
{
    return \Core\Helpers\FileHelpers::dirname($path);
}

function file_mime_type($path): string
{
    return \Core\Helpers\FileHelpers::mimeType($path);
}

function file_is_image($path): bool
{
    return \Core\Helpers\FileHelpers::isImage($path);
}

function file_is_pdf($path): bool
{
    return \Core\Helpers\FileHelpers::isPdf($path);
}

function file_is_text($path): bool
{
    return \Core\Helpers\FileHelpers::isText($path);
}

function file_is_video($path): bool
{
    return \Core\Helpers\FileHelpers::isVideo($path);
}

function file_is_audio($path): bool
{
    return \Core\Helpers\FileHelpers::isAudio($path);
}

function file_is_archive($path): bool
{
    return \Core\Helpers\FileHelpers::isArchive($path);
}

/**
 * Validation helpers - yeni ValidationHelpers sınıfını kullan
 */
function validate_email(string $email): bool
{
    return \Core\Helpers\ValidationHelpers::email($email);
}

function validate_url(string $url): bool
{
    return \Core\Helpers\ValidationHelpers::url($url);
}

function validate_ip(string $ip): bool
{
    return \Core\Helpers\ValidationHelpers::ip($ip);
}

function validate_alpha(string $value): bool
{
    return \Core\Helpers\ValidationHelpers::alpha($value);
}

function validate_alpha_numeric(string $value): bool
{
    return \Core\Helpers\ValidationHelpers::alphaNumeric($value);
}

function validate_numeric($value): bool
{
    return \Core\Helpers\ValidationHelpers::numeric($value);
}

function validate_integer($value): bool
{
    return \Core\Helpers\ValidationHelpers::integer($value);
}

function validate_float($value): bool
{
    return \Core\Helpers\ValidationHelpers::float($value);
}

function validate_boolean($value): bool
{
    return \Core\Helpers\ValidationHelpers::boolean($value);
}

function validate_date(string $date, string $format = 'Y-m-d'): bool
{
    return \Core\Helpers\ValidationHelpers::date($date, $format);
}

function validate_min_length(string $value, int $min): bool
{
    return \Core\Helpers\ValidationHelpers::minLength($value, $min);
}

function validate_max_length(string $value, int $max): bool
{
    return \Core\Helpers\ValidationHelpers::maxLength($value, $max);
}

function validate_length(string $value, int $length): bool
{
    return \Core\Helpers\ValidationHelpers::length($value, $length);
}

function validate_between($value, $min, $max): bool
{
    return \Core\Helpers\ValidationHelpers::between($value, $min, $max);
}

function validate_in($value, array $allowed): bool
{
    return \Core\Helpers\ValidationHelpers::in($value, $allowed);
}

function validate_not_in($value, array $forbidden): bool
{
    return \Core\Helpers\ValidationHelpers::notIn($value, $forbidden);
}

function validate_unique(string $table, string $column, $value, $ignore = null): bool
{
    return \Core\Helpers\ValidationHelpers::unique($table, $column, $value, $ignore);
}

function validate_exists(string $table, string $column, $value): bool
{
    return \Core\Helpers\ValidationHelpers::exists($table, $column, $value);
}

/**
 * Cache helpers - yeni CacheHelpers sınıfını kullan
 */
function cache_get(string $key, $default = null)
{
    return \Core\Helpers\CacheHelpers::get($key, $default);
}

function cache_set(string $key, $value, int $ttl = 3600): bool
{
    return \Core\Helpers\CacheHelpers::set($key, $value, $ttl);
}

function cache_has(string $key): bool
{
    return \Core\Helpers\CacheHelpers::has($key);
}

function cache_delete(string $key): bool
{
    return \Core\Helpers\CacheHelpers::delete($key);
}

function cache_clear(): bool
{
    return \Core\Helpers\CacheHelpers::clear();
}

function cache_remember(string $key, int $ttl, callable $callback)
{
    return \Core\Helpers\CacheHelpers::remember($key, $ttl, $callback);
}

function cache_remember_forever(string $key, callable $callback)
{
    return \Core\Helpers\CacheHelpers::rememberForever($key, $callback);
}

function cache_increment(string $key, int $value = 1): int
{
    return \Core\Helpers\CacheHelpers::increment($key, $value);
}

function cache_decrement(string $key, int $value = 1): int
{
    return \Core\Helpers\CacheHelpers::decrement($key, $value);
}

/**
 * Logging helpers
 */
function log_info(string $message, array $context = []): void
{
    error_log('[INFO] ' . $message . ' ' . json_encode($context));
}

function log_error(string $message, array $context = []): void
{
    error_log('[ERROR] ' . $message . ' ' . json_encode($context));
}

function log_warning(string $message, array $context = []): void
{
    error_log('[WARNING] ' . $message . ' ' . json_encode($context));
}

function log_debug(string $message, array $context = []): void
{
    error_log('[DEBUG] ' . $message . ' ' . json_encode($context));
}

function log_critical(string $message, array $context = []): void
{
    error_log('[CRITICAL] ' . $message . ' ' . json_encode($context));
}

function log_alert(string $message, array $context = []): void
{
    error_log('[ALERT] ' . $message . ' ' . json_encode($context));
}

function log_emergency(string $message, array $context = []): void
{
    error_log('[EMERGENCY] ' . $message . ' ' . json_encode($context));
}

/**
 * Debug helpers
 */
if (!function_exists('dd')) {
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            dump($var);
        }
        exit;
    }
}

if (!function_exists('dump')) {
    function dump($var): void
    {
        var_dump($var);
    }
}

function dump_and_die($var): void
{
    dump($var);
    exit;
}

function debug_backtrace_simple(): array
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $result = [];
    foreach ($trace as $item) {
        $result[] = [
            'file' => $item['file'] ?? '',
            'line' => $item['line'] ?? '',
            'function' => $item['function'] ?? '',
            'class' => $item['class'] ?? '',
        ];
    }
    return $result;
}

function debug_memory_usage(): string
{
    return format_bytes(memory_get_usage(true));
}

function debug_peak_memory_usage(): string
{
    return format_bytes(memory_get_peak_usage(true));
}

function debug_execution_time(): float
{
    return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
}

/**
 * Security helpers
 */
function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function generate_token(): string
{
    return bin2hex(random_bytes(32));
}

if (!function_exists('hash_equals_custom')) {
    function hash_equals_custom(string $known_string, string $user_string): bool
    {
        return hash_equals($known_string, $user_string);
    }
}

function encrypt($value): string
{
    return container()->make('encryption')->encrypt($value);
}

function decrypt(string $value)
{
    return container()->make('encryption')->decrypt($value);
}

/**
 * View helpers
 */
function view(string $view, array $data = []): string
{
    return container()->make(AdvancedView::class)->render($view, $data);
}

function view_exists(string $view): bool
{
    return container()->make(AdvancedView::class)->exists($view);
}

function view_share(string $key, $value): void
{
    container()->make(AdvancedView::class)->share($key, $value);
}

function view_share_data(array $data): void
{
    container()->make(AdvancedView::class)->shareData($data);
}

/**
 * Response helpers
 */
function response($content = '', int $status = 200, array $headers = []): \Symfony\Component\HttpFoundation\Response
{
    return new \Symfony\Component\HttpFoundation\Response($content, $status, $headers);
}

function json_response($data, int $status = 200, array $headers = []): \Symfony\Component\HttpFoundation\JsonResponse
{
    return new \Symfony\Component\HttpFoundation\JsonResponse($data, $status, $headers);
}

function redirect_response(string $url, int $status = 302, array $headers = []): \Symfony\Component\HttpFoundation\RedirectResponse
{
    return new \Symfony\Component\HttpFoundation\RedirectResponse($url, $status, $headers);
}

function file_response(string $path, string $name = null, array $headers = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
{
    return new \Symfony\Component\HttpFoundation\BinaryFileResponse($path, 200, $headers, false, $name);
}

function stream_response($callback, int $status = 200, array $headers = []): \Symfony\Component\HttpFoundation\StreamedResponse
{
    return new \Symfony\Component\HttpFoundation\StreamedResponse($callback, $status, $headers);
}

/**
 * Database helpers
 */
function db(): \PDO
{
    return container()->make('database');
}

function db_query(string $sql, array $params = []): \PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_fetch(string $sql, array $params = []): array
{
    return db_query($sql, $params)->fetch(\PDO::FETCH_ASSOC);
}

function db_fetch_all(string $sql, array $params = []): array
{
    return db_query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
}

function db_insert(string $table, array $data): int
{
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    db_query($sql, $data);
    return db()->lastInsertId();
}

function db_update(string $table, array $data, string $where, array $whereParams = []): int
{
    $setParts = [];
    foreach (array_keys($data) as $column) {
        $setParts[] = "{$column} = :{$column}";
    }
    $setClause = implode(', ', $setParts);
    $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
    $stmt = db_query($sql, array_merge($data, $whereParams));
    return $stmt->rowCount();
}

function db_delete(string $table, string $where, array $params = []): int
{
    $sql = "DELETE FROM {$table} WHERE {$where}";
    $stmt = db_query($sql, $params);
    return $stmt->rowCount();
}

function db_count(string $table, string $where = '1', array $params = []): int
{
    $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
    $result = db_fetch($sql, $params);
    return (int) $result['count'];
}

function db_exists(string $table, string $where, array $params = []): bool
{
    return db_count($table, $where, $params) > 0;
}

/**
 * Authentication helpers
 */
function auth()
{
    return container()->make('auth');
}

function user()
{
    return auth()->user();
}

function check(): bool
{
    return auth()->check();
}

function guest(): bool
{
    return !check();
}

function can(string $permission): bool
{
    return auth()->can($permission);
}

function has_role(string $role): bool
{
    return auth()->hasRole($role);
}

function has_permission(string $permission): bool
{
    return auth()->hasPermission($permission);
}

/**
 * Type checking helpers - PHP built-in fonksiyonları için kontrol
 */
if (!function_exists('is_null_custom')) {
    function is_null_custom($value): bool
    {
        return is_null($value);
    }
}

if (!function_exists('is_empty_custom')) {
    function is_empty_custom($value): bool
    {
        return empty($value);
    }
}

if (!function_exists('is_array_custom')) {
    function is_array_custom($value): bool
    {
        return is_array($value);
    }
}

if (!function_exists('is_string_custom')) {
    function is_string_custom($value): bool
    {
        return is_string($value);
    }
}

if (!function_exists('is_numeric_custom')) {
    function is_numeric_custom($value): bool
    {
        return is_numeric($value);
    }
}

if (!function_exists('is_bool_custom')) {
    function is_bool_custom($value): bool
    {
        return is_bool($value);
    }
}

if (!function_exists('is_object_custom')) {
    function is_object_custom($value): bool
    {
        return is_object($value);
    }
}

if (!function_exists('is_callable_custom')) {
    function is_callable_custom($value): bool
    {
        return is_callable($value);
    }
}

if (!function_exists('is_file_custom')) {
    function is_file_custom($value): bool
    {
        return is_file($value);
    }
}

if (!function_exists('is_dir_custom')) {
    function is_dir_custom($value): bool
    {
        return is_dir($value);
    }
}

if (!function_exists('is_readable_custom')) {
    function is_readable_custom($value): bool
    {
        return is_readable($value);
    }
}

if (!function_exists('is_writable_custom')) {
    function is_writable_custom($value): bool
    {
        return is_writable($value);
    }
}

if (!function_exists('is_executable_custom')) {
    function is_executable_custom($value): bool
    {
        return is_executable($value);
    }
}

if (!function_exists('is_link_custom')) {
    function is_link_custom($value): bool
    {
        return is_link($value);
    }
}

if (!function_exists('is_uploaded_file_custom')) {
    function is_uploaded_file_custom($value): bool
    {
        return is_uploaded_file($value);
    }
}

/**
 * Environment helpers
 */
function is_ssl(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
}

function is_ajax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function is_cli(): bool
{
    return php_sapi_name() === 'cli';
}

function is_windows(): bool
{
    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

function is_mac(): bool
{
    return strtoupper(PHP_OS) === 'DARWIN';
}

function is_linux(): bool
{
    return strtoupper(PHP_OS) === 'LINUX';
}

/**
 * Performance helpers
 */
function benchmark(callable $callback): float
{
    $start = microtime(true);
    $callback();
    $end = microtime(true);
    return ($end - $start) * 1000; // Return milliseconds
}

function benchmark_with_result(callable $callback): array
{
    $start = microtime(true);
    $result = $callback();
    $end = microtime(true);
    $time = ($end - $start) * 1000;
    
    return [
        'result' => $result,
        'time' => $time,
        'memory' => memory_get_usage(true),
        'peak_memory' => memory_get_peak_usage(true)
    ];
}

function memory_usage(): string
{
    return format_bytes(memory_get_usage(true));
}

function peak_memory_usage(): string
{
    return format_bytes(memory_get_peak_usage(true));
}

function execution_time(): float
{
    return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
}

/**
 * Path helpers
 */
function storage_path(string $path = ''): string
{
    return __DIR__ . '/../storage' . ($path ? '/' . $path : '');
}

function database_path(string $path = ''): string
{
    return __DIR__ . '/../database' . ($path ? '/' . $path : '');
}

function resource_path(string $path = ''): string
{
    return __DIR__ . '/../resources' . ($path ? '/' . $path : '');
}

function app_path(string $path = ''): string
{
    return __DIR__ . '/../app' . ($path ? '/' . $path : '');
}

function config_path(string $path = ''): string
{
    return __DIR__ . '/../config' . ($path ? '/' . $path : '');
}

function public_path(string $path = ''): string
{
    return __DIR__ . '/../public' . ($path ? '/' . $path : '');
}