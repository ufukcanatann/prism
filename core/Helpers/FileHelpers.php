<?php

namespace Core\Helpers;

class FileHelpers
{
    public static function exists($path): bool
    {
        return file_exists($path);
    }

    public static function size($path): int
    {
        return filesize($path);
    }

    public static function extension($path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public static function name($path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    public static function basename($path): string
    {
        return basename($path);
    }

    public static function dirname($path): string
    {
        return dirname($path);
    }

    public static function mimeType($path): string
    {
        return mime_content_type($path);
    }

    public static function isImage($path): bool
    {
        $extension = strtolower(self::extension($path));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
    }

    public static function isPdf($path): bool
    {
        return strtolower(self::extension($path)) === 'pdf';
    }

    public static function isText($path): bool
    {
        $extension = strtolower(self::extension($path));
        return in_array($extension, ['txt', 'md', 'html', 'css', 'js', 'php', 'json', 'xml', 'csv']);
    }

    public static function isVideo($path): bool
    {
        $extension = strtolower(self::extension($path));
        return in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv']);
    }

    public static function isAudio($path): bool
    {
        $extension = strtolower(self::extension($path));
        return in_array($extension, ['mp3', 'wav', 'ogg', 'flac', 'aac', 'wma']);
    }

    public static function isArchive($path): bool
    {
        $extension = strtolower(self::extension($path));
        return in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz', 'bz2']);
    }
}
