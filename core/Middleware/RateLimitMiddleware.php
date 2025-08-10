<?php

namespace Core\Middleware;

use Core\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * Maximum requests per minute
     */
    private int $maxAttempts;
    
    /**
     * Rate limit window in minutes
     */
    private int $windowMinutes;
    
    /**
     * Cache key prefix
     */
    private string $keyPrefix = 'rate_limit:';
    
    /**
     * Constructor
     */
    public function __construct(int $maxAttempts = 60, int $windowMinutes = 1)
    {
        $this->maxAttempts = $maxAttempts;
        $this->windowMinutes = $windowMinutes;
    }
    
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Response $response = null)
    {
        $key = $this->generateKey($request);
        $attempts = $this->getAttempts($key);
        
        if ($attempts >= $this->maxAttempts) {
            return $this->handleRateLimitExceeded($request, $attempts);
        }
        
        $this->incrementAttempts($key);
        
        // Add rate limit headers to response
        if ($response) {
            $response->headers->set('X-RateLimit-Limit', $this->maxAttempts);
            $response->headers->set('X-RateLimit-Remaining', max(0, $this->maxAttempts - $attempts - 1));
            $response->headers->set('X-RateLimit-Reset', $this->getResetTime($key));
        }
        
        return $response;
    }
    
    /**
     * Generate cache key for rate limiting
     */
    private function generateKey(Request $request): string
    {
        $ip = $this->getClientIp($request);
        $route = $request->getPathInfo();
        
        return $this->keyPrefix . md5($ip . '|' . $route);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp(Request $request): string
    {
        // Check for IP from proxy headers
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get current attempts for key
     */
    private function getAttempts(string $key): int
    {
        $data = $this->getFromCache($key);
        
        if (!$data) {
            return 0;
        }
        
        // Check if window has expired
        if (time() > $data['reset_time']) {
            $this->clearFromCache($key);
            return 0;
        }
        
        return $data['attempts'] ?? 0;
    }
    
    /**
     * Increment attempts for key
     */
    private function incrementAttempts(string $key): void
    {
        $data = $this->getFromCache($key);
        
        if (!$data) {
            $data = [
                'attempts' => 0,
                'reset_time' => time() + ($this->windowMinutes * 60)
            ];
        }
        
        $data['attempts']++;
        
        $this->storeInCache($key, $data);
    }
    
    /**
     * Get reset time for key
     */
    private function getResetTime(string $key): int
    {
        $data = $this->getFromCache($key);
        return $data['reset_time'] ?? (time() + ($this->windowMinutes * 60));
    }
    
    /**
     * Handle rate limit exceeded
     */
    private function handleRateLimitExceeded(Request $request, int $attempts): Response
    {
        $retryAfter = $this->windowMinutes * 60;
        
        if ($this->expectsJson($request)) {
            return new Response(
                json_encode([
                    'error' => 'Too Many Requests',
                    'message' => 'Rate limit exceeded. Please try again later.',
                    'retry_after' => $retryAfter
                ]),
                429,
                [
                    'Content-Type' => 'application/json',
                    'Retry-After' => $retryAfter,
                    'X-RateLimit-Limit' => $this->maxAttempts,
                    'X-RateLimit-Remaining' => 0,
                    'X-RateLimit-Reset' => time() + $retryAfter
                ]
            );
        }
        
        return new Response(
            '<!DOCTYPE html>
            <html>
            <head>
                <title>Rate Limit Exceeded</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                    .error { color: #d32f2f; margin: 20px 0; }
                    .info { color: #1976d2; margin: 20px 0; }
                </style>
            </head>
            <body>
                <h1>Rate Limit Exceeded</h1>
                <p class="error">Too many requests. Please slow down.</p>
                <p class="info">You can try again in ' . $this->windowMinutes . ' minute(s).</p>
                <p>Limit: ' . $this->maxAttempts . ' requests per ' . $this->windowMinutes . ' minute(s)</p>
            </body>
            </html>',
            429,
            [
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $this->maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => time() + $retryAfter
            ]
        );
    }
    
    /**
     * Check if request expects JSON response
     */
    private function expectsJson(Request $request): bool
    {
        $accept = $request->headers->get('Accept', '');
        return strpos($accept, 'application/json') !== false || $request->isXmlHttpRequest();
    }
    
    /**
     * Store data in cache (using session for now, can be extended to Redis/Memcached)
     */
    private function storeInCache(string $key, array $data): void
    {
        $cacheData = Session::get('rate_limit_cache', []);
        $cacheData[$key] = $data;
        Session::set('rate_limit_cache', $cacheData);
    }
    
    /**
     * Get data from cache
     */
    private function getFromCache(string $key): ?array
    {
        $cacheData = Session::get('rate_limit_cache', []);
        return $cacheData[$key] ?? null;
    }
    
    /**
     * Clear data from cache
     */
    private function clearFromCache(string $key): void
    {
        $cacheData = Session::get('rate_limit_cache', []);
        unset($cacheData[$key]);
        Session::set('rate_limit_cache', $cacheData);
    }
    
    /**
     * Create rate limiter with custom settings
     */
    public static function create(int $maxAttempts, int $windowMinutes = 1): self
    {
        return new self($maxAttempts, $windowMinutes);
    }
}
