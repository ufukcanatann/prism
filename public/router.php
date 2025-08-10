<?php
/**
 * PHP Built-in Server Router
 * This file handles static file serving for PHP's built-in server
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Static file exists, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    // Get file extension
    $ext = pathinfo($uri, PATHINFO_EXTENSION);
    
    // Set appropriate content type
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject'
    ];
    
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    
    return false; // Let PHP serve the file
}

// Not a static file, include index.php
require_once __DIR__ . '/index.php';
