<?php

namespace Core\Security;

use Core\Session;

class CsrfProtection
{
    /**
     * CSRF token key in session
     */
    private const TOKEN_KEY = '_csrf_token';
    
    /**
     * Token expiry time in seconds (default: 2 hours)
     */
    private const TOKEN_EXPIRY = 7200;
    
    /**
     * Generate a new CSRF token
     */
    public static function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + self::TOKEN_EXPIRY;
        
        Session::set(self::TOKEN_KEY, [
            'token' => $token,
            'expiry' => $expiry
        ]);
        
        return $token;
    }
    
    /**
     * Get current CSRF token
     */
    public static function getToken(): ?string
    {
        $data = Session::get(self::TOKEN_KEY);
        
        if (!$data || !isset($data['token'], $data['expiry'])) {
            return null;
        }
        
        // Check if token is expired
        if (time() > $data['expiry']) {
            Session::remove(self::TOKEN_KEY);
            return null;
        }
        
        return $data['token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateToken(string $token): bool
    {
        $storedData = Session::get(self::TOKEN_KEY);
        
        if (!$storedData || !isset($storedData['token'], $storedData['expiry'])) {
            return false;
        }
        
        // Check if token is expired
        if (time() > $storedData['expiry']) {
            Session::remove(self::TOKEN_KEY);
            return false;
        }
        
        return hash_equals($storedData['token'], $token);
    }
    
    /**
     * Get token or generate new one
     */
    public static function token(): string
    {
        $token = self::getToken();
        
        if ($token === null) {
            $token = self::generateToken();
        }
        
        return $token;
    }
    
    /**
     * Create CSRF hidden input field
     */
    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Create CSRF meta tag
     */
    public static function metaTag(): string
    {
        $token = self::token();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Validate request CSRF token
     */
    public static function validateRequest(): bool
    {
        // Get token from POST data
        $token = $_POST['_token'] ?? null;
        
        // If not in POST, check headers
        if (!$token) {
            $headers = getallheaders();
            $token = $headers['X-CSRF-TOKEN'] ?? $headers['X-Requested-With'] ?? null;
        }
        
        if (!$token) {
            return false;
        }
        
        return self::validateToken($token);
    }
    
    /**
     * Clear CSRF token
     */
    public static function clearToken(): void
    {
        Session::remove(self::TOKEN_KEY);
    }
    
    /**
     * Check if current request needs CSRF protection
     */
    public static function shouldValidate(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $exemptMethods = ['GET', 'HEAD', 'OPTIONS'];
        
        return !in_array(strtoupper($method), $exemptMethods);
    }
}
