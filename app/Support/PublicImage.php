<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

final class PublicImage
{
    public static function url(?string $storedPath, string $fallbackAsset): string
    {
        $relativePath = self::relativePath($storedPath);

        return $relativePath && self::isRenderable($relativePath)
            ? asset('storage/'.$relativePath)
            : asset(ltrim($fallbackAsset, '/'));
    }

    public static function relativePath(?string $storedPath): ?string
    {
        if (! $storedPath) {
            return null;
        }

        $path = str_replace('\\', '/', trim($storedPath));
        $path = preg_replace('#^(?:/?storage/)+#i', '', $path);
        $path = ltrim((string) $path, '/');

        return $path !== '' && ! str_contains($path, '..') ? $path : null;
    }

    private static function isRenderable(string $relativePath): bool
    {
        try {
            $disk = Storage::disk('public');
            if (! $disk->exists($relativePath)) {
                return false;
            }

            $expectedMime = match (strtolower(pathinfo($relativePath, PATHINFO_EXTENSION))) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => null,
            };

            return $expectedMime !== null && $disk->mimeType($relativePath) === $expectedMime;
        } catch (Throwable) {
            return false;
        }
    }
}
