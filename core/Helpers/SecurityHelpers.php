<?php

if (!function_exists('csrf_token')) {
    /**
     * Get CSRF token
     */
    function csrf_token(): string
    {
        return \Core\Security\CsrfProtection::token();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF hidden input field
     */
    function csrf_field(): string
    {
        return \Core\Security\CsrfProtection::field();
    }
}

if (!function_exists('csrf_meta')) {
    /**
     * Generate CSRF meta tag
     */
    function csrf_meta(): string
    {
        return \Core\Security\CsrfProtection::metaTag();
    }
}

if (!function_exists('xss_clean')) {
    /**
     * Clean input from XSS
     */
    function xss_clean(string $input, bool $allowHtml = false): string
    {
        return \Core\Security\XssProtection::clean($input, $allowHtml);
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities
     */
    function e(string $value): string
    {
        return \Core\Security\XssProtection::escape($value);
    }
}
