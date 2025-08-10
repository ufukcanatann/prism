<?php

namespace Core\Helpers;

/**
 * String Helper Functions
 */
class StringHelpers
{
    /**
     * Check if string contains substring
     */
    public static function contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }

    /**
     * Check if string starts with substring
     */
    public static function starts_with(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) === 0;
    }

    /**
     * Check if string ends with substring
     */
    public static function ends_with(string $haystack, string $needle): bool
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }

    /**
     * Get string before first occurrence
     */
    public static function before(string $subject, string $search): string
    {
        $pos = strpos($subject, $search);
        return $pos === false ? $subject : substr($subject, 0, $pos);
    }

    /**
     * Get string after first occurrence
     */
    public static function after(string $subject, string $search): string
    {
        $pos = strpos($subject, $search);
        return $pos === false ? $subject : substr($subject, $pos + strlen($search));
    }

    /**
     * Get string between two substrings
     */
    public static function between(string $subject, string $from, string $to): string
    {
        return self::before(self::after($subject, $from), $to);
    }

    /**
     * Replace first occurrence
     */
    public static function replace_first(string $search, string $replace, string $subject): string
    {
        $pos = strpos($subject, $search);
        if ($pos !== false) {
            return substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }

    /**
     * Replace last occurrence
     */
    public static function replace_last(string $search, string $replace, string $subject): string
    {
        $pos = strrpos($subject, $search);
        if ($pos !== false) {
            return substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }

    /**
     * Limit string length
     */
    public static function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (strlen($value) <= $limit) {
            return $value;
        }
        return substr($value, 0, $limit) . $end;
    }

    /**
     * Generate random string
     */
    public static function random(int $length = 16): string
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return $string;
    }

    /**
     * Convert to lowercase
     */
    public static function lower(string $value): string
    {
        return strtolower($value);
    }

    /**
     * Convert to uppercase
     */
    public static function upper(string $value): string
    {
        return strtoupper($value);
    }

    /**
     * Convert first character to uppercase
     */
    public static function ucfirst(string $value): string
    {
        return ucfirst($value);
    }

    /**
     * Convert first character of each word to uppercase
     */
    public static function ucwords(string $value): string
    {
        return ucwords($value);
    }

    /**
     * Convert to title case
     */
    public static function title(string $value): string
    {
        return ucwords(strtolower($value));
    }

    /**
     * Convert to slug
     */
    public static function slug(string $value, string $separator = '-'): string
    {
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9\s-]/', '', $value);
        $value = preg_replace('/[\s-]+/', $separator, $value);
        return trim($value, $separator);
    }

    /**
     * Convert to camel case
     */
    public static function camel(string $value): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value))));
    }

    /**
     * Convert to snake case
     */
    public static function snake(string $value): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
    }

    /**
     * Convert to kebab case
     */
    public static function kebab(string $value): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $value));
    }

    /**
     * Pluralize word
     */
    public static function plural(string $value): string
    {
        $irregulars = [
            'child' => 'children',
            'foot' => 'feet',
            'person' => 'people',
            'man' => 'men',
            'woman' => 'women',
            'tooth' => 'teeth',
            'mouse' => 'mice',
            'goose' => 'geese',
        ];

        if (isset($irregulars[strtolower($value)])) {
            return $irregulars[strtolower($value)];
        }

        $last = strtolower($value[strlen($value) - 1]);
        switch ($last) {
            case 'y':
                return substr($value, 0, -1) . 'ies';
            case 's':
            case 'x':
            case 'z':
                return $value . 'es';
            case 'h':
                $second = strtolower($value[strlen($value) - 2]);
                if ($second == 's' || $second == 'c') {
                    return $value . 'es';
                }
            default:
                return $value . 's';
        }
    }

    /**
     * Singularize word
     */
    public static function singular(string $value): string
    {
        $irregulars = [
            'children' => 'child',
            'feet' => 'foot',
            'people' => 'person',
            'men' => 'man',
            'women' => 'woman',
            'teeth' => 'tooth',
            'mice' => 'mouse',
            'geese' => 'goose',
        ];

        if (isset($irregulars[strtolower($value)])) {
            return $irregulars[strtolower($value)];
        }

        if (substr($value, -3) == 'ies') {
            return substr($value, 0, -3) . 'y';
        }

        if (substr($value, -2) == 'es') {
            return substr($value, 0, -2);
        }

        if (substr($value, -1) == 's') {
            return substr($value, 0, -1);
        }

        return $value;
    }

    /**
     * Generate random words
     */
    public static function random_words(int $count = 3): string
    {
        $words = ['lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit', 'sed', 'do', 'eiusmod', 'tempor', 'incididunt', 'ut', 'labore', 'et', 'dolore', 'magna', 'aliqua'];
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $words[array_rand($words)];
        }
        return implode(' ', $result);
    }

    /**
     * Generate random sentence
     */
    public static function random_sentence(int $wordCount = 5): string
    {
        return ucfirst(self::random_words($wordCount)) . '.';
    }

    /**
     * Generate random paragraph
     */
    public static function random_paragraph(int $sentenceCount = 3): string
    {
        $sentences = [];
        for ($i = 0; $i < $sentenceCount; $i++) {
            $sentences[] = self::random_sentence(rand(5, 15));
        }
        return implode(' ', $sentences);
    }
}
