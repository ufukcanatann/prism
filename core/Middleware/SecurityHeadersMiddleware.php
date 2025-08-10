<?php

namespace Core\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    /**
     * Security headers configuration
     */
    private array $headers = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ];
    
    /**
     * Content Security Policy
     */
    private string $csp = "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self'; font-src 'self'; object-src 'none'; media-src 'self'; frame-src 'none';";
    
    /**
     * Strict Transport Security
     */
    private string $hsts = 'max-age=31536000; includeSubDomains';
    
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Response $response = null)
    {
        if ($response) {
            // Add security headers
            foreach ($this->headers as $header => $value) {
                $response->headers->set($header, $value);
            }
            
            // Add Content Security Policy
            $response->headers->set('Content-Security-Policy', $this->csp);
            
            // Add HSTS for HTTPS requests
            if ($request->isSecure()) {
                $response->headers->set('Strict-Transport-Security', $this->hsts);
            }
            
            // Remove server information
            $response->headers->remove('Server');
            $response->headers->remove('X-Powered-By');
        }
        
        return $response;
    }
    
    /**
     * Set custom CSP
     */
    public function setContentSecurityPolicy(string $csp): self
    {
        $this->csp = $csp;
        return $this;
    }
    
    /**
     * Set custom HSTS
     */
    public function setStrictTransportSecurity(string $hsts): self
    {
        $this->hsts = $hsts;
        return $this;
    }
    
    /**
     * Add custom header
     */
    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Remove header
     */
    public function removeHeader(string $name): self
    {
        unset($this->headers[$name]);
        return $this;
    }
}
