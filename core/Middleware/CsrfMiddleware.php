<?php

namespace Core\Middleware;

use Core\Security\CsrfProtection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Response $response = null)
    {
        // Skip CSRF validation for safe methods
        if (!CsrfProtection::shouldValidate()) {
            return $response;
        }
        
        // Skip for API routes if configured
        if ($this->isApiRoute($request)) {
            return $response;
        }
        
        // Validate CSRF token
        if (!CsrfProtection::validateRequest()) {
            return $this->handleCsrfFailure($request);
        }
        
        return $response;
    }
    
    /**
     * Check if this is an API route
     */
    private function isApiRoute(Request $request): bool
    {
        $path = $request->getPathInfo();
        return strpos($path, '/api/') === 0;
    }
    
    /**
     * Handle CSRF validation failure
     */
    private function handleCsrfFailure(Request $request): Response
    {
        if ($this->expectsJson($request)) {
            return new Response(
                json_encode([
                    'error' => 'CSRF token mismatch',
                    'message' => 'The CSRF token is invalid or expired'
                ]),
                419,
                ['Content-Type' => 'application/json']
            );
        }
        
        // For regular requests, redirect back with error
        $referer = $request->headers->get('referer', '/');
        
        return new Response(
            '<!DOCTYPE html>
            <html>
            <head>
                <title>CSRF Token Mismatch</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                    .error { color: #d32f2f; margin: 20px 0; }
                    .button { background: #1976d2; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; }
                </style>
            </head>
            <body>
                <h1>Security Error</h1>
                <p class="error">CSRF token mismatch. Your request could not be processed.</p>
                <p>This might happen if:</p>
                <ul style="text-align: left; display: inline-block;">
                    <li>Your session has expired</li>
                    <li>You have multiple tabs open</li>
                    <li>You took too long to submit the form</li>
                </ul>
                <p><a href="' . htmlspecialchars($referer) . '" class="button">Go Back</a></p>
            </body>
            </html>',
            419
        );
    }
    
    /**
     * Check if request expects JSON response
     */
    private function expectsJson(Request $request): bool
    {
        $accept = $request->headers->get('Accept', '');
        $contentType = $request->headers->get('Content-Type', '');
        
        return strpos($accept, 'application/json') !== false ||
               strpos($contentType, 'application/json') !== false ||
               $request->isXmlHttpRequest();
    }
}
