<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Upload an image, resize it and save as webp.
     */
    public static function uploadAndResize(mixed $file, string $directory, string $resizeMethod = 'scaleDown', int $width = 800, ?int $height = null, int $quality = 80, string $disk = 'public'): string
    {
        $filename = Str::uuid() . '.webp';

        $image = Image::read(is_string($file) ? $file : $file->getRealPath());

        if ($resizeMethod === 'cover') {
            $image->cover($width, $height ?? $width);
        } elseif ($resizeMethod === 'scaleDown') {
            $image->scaleDown(width: $width);
        }

        $path = trim($directory, '/') . '/' . $filename;
        Storage::disk($disk)->put($path, (string) $image->toWebp($quality));

        return $path;
    }

    /**
     * Delete an image from storage
     */
    public static function delete(?string $path, string $disk = 'public'): void
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * Upload an image with multiple sizes (variations).
     *
     * @param mixed $file
     * @param string $baseDirectory
     * @param array $variations e.g. ['large' => ['width' => 800], 'thumb' => ['width' => 400]]
     */
    public static function uploadVariations(mixed $file, string $baseDirectory, array $variations, string $disk = 'public'): array
    {
        $filename = Str::uuid() . '.webp';
        $paths = [];

        foreach ($variations as $prefix => $options) {
            $image = Image::read(is_string($file) ? $file : $file->getRealPath());
            $method = $options['method'] ?? 'scaleDown';
            $width = $options['width'] ?? 800;
            $height = $options['height'] ?? $width;

            if ($method === 'cover') {
                $image->cover($width, $height);
            } elseif ($method === 'scaleDown') {
                $image->scaleDown(width: $width);
            }

            $path = trim($baseDirectory, '/') . '/' . trim($prefix, '/') . '/' . $filename;
            Storage::disk($disk)->put($path, (string) $image->toWebp($options['quality'] ?? 80));
            $paths[$prefix] = $path;
        }

        return $paths;
    }
}
