<?php

namespace Core\Security;

class XssProtection
{
    /**
     * Allowed HTML tags for filtering
     */
    private static array $allowedTags = [
        'p', 'br', 'strong', 'em', 'u', 'i', 'b',
        'ul', 'ol', 'li', 'a', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
    ];
    
    /**
     * Allowed attributes for specific tags
     */
    private static array $allowedAttributes = [
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'title', 'width', 'height']
    ];
    
    /**
     * Clean input from XSS attacks
     */
    public static function clean(string $input, bool $allowHtml = false): string
    {
        if (!$allowHtml) {
            return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return self::filterHtml($input);
    }
    
    /**
     * Clean array of inputs
     */
    public static function cleanArray(array $data, bool $allowHtml = false): array
    {
        $cleaned = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $cleaned[$key] = self::cleanArray($value, $allowHtml);
            } elseif (is_string($value)) {
                $cleaned[$key] = self::clean($value, $allowHtml);
            } else {
                $cleaned[$key] = $value;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Filter HTML content
     */
    private static function filterHtml(string $html): string
    {
        // Remove dangerous tags completely
        $dangerousTags = [
            'script', 'object', 'embed', 'link', 'style', 'iframe',
            'frame', 'frameset', 'form', 'input', 'button', 'textarea',
            'select', 'option', 'meta', 'base', 'title', 'head'
        ];
        
        foreach ($dangerousTags as $tag) {
            $html = preg_replace('/<' . $tag . '\b[^>]*>.*?<\/' . $tag . '>/is', '', $html);
            $html = preg_replace('/<' . $tag . '\b[^>]*\/?>/is', '', $html);
        }
        
        // Remove javascript: and data: URLs
        $html = preg_replace('/javascript:/i', '', $html);
        $html = preg_replace('/data:/i', '', $html);
        $html = preg_replace('/vbscript:/i', '', $html);
        
        // Remove event handlers
        $html = preg_replace('/on\w+\s*=/i', '', $html);
        
        // Filter allowed tags and attributes
        $html = self::filterAllowedTags($html);
        
        return $html;
    }
    
    /**
     * Filter only allowed tags and attributes
     */
    private static function filterAllowedTags(string $html): string
    {
        // Use DOMDocument for proper HTML parsing
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//*');
        
        $nodesToRemove = [];
        
        foreach ($nodes as $node) {
            if (!($node instanceof \DOMElement)) {
                continue;
            }
            
            $tagName = strtolower($node->nodeName);
            
            // Remove if tag is not allowed
            if (!in_array($tagName, self::$allowedTags)) {
                $nodesToRemove[] = $node;
                continue;
            }
            
            // Filter attributes
            if ($node->hasAttributes()) {
                $attributesToRemove = [];
                
                foreach ($node->attributes as $attr) {
                    $attrName = strtolower($attr->nodeName);
                    $allowedAttrs = self::$allowedAttributes[$tagName] ?? [];
                    
                    if (!in_array($attrName, $allowedAttrs)) {
                        $attributesToRemove[] = $attrName;
                    }
                }
                
                foreach ($attributesToRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }
        
        // Remove disallowed nodes
        foreach ($nodesToRemove as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }
        
        return $dom->saveHTML();
    }
    
    /**
     * Escape output for safe display
     */
    public static function escape(string $output): string
    {
        return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Safe echo function
     */
    public static function e(string $output): void
    {
        echo self::escape($output);
    }
    
    /**
     * Validate if string contains potential XSS
     */
    public static function hasXss(string $input): bool
    {
        $dangerous = [
            '<script', '</script>', 'javascript:', 'vbscript:', 'data:',
            'onload=', 'onerror=', 'onclick=', 'onmouseover=', 'onfocus=',
            'onblur=', 'onchange=', 'onsubmit=', 'expression('
        ];
        
        $input = strtolower($input);
        
        foreach ($dangerous as $pattern) {
            if (strpos($input, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Configure allowed tags
     */
    public static function setAllowedTags(array $tags): void
    {
        self::$allowedTags = $tags;
    }
    
    /**
     * Configure allowed attributes
     */
    public static function setAllowedAttributes(array $attributes): void
    {
        self::$allowedAttributes = $attributes;
    }
    
    /**
     * Get current allowed tags
     */
    public static function getAllowedTags(): array
    {
        return self::$allowedTags;
    }
    
    /**
     * Get current allowed attributes
     */
    public static function getAllowedAttributes(): array
    {
        return self::$allowedAttributes;
    }
}
