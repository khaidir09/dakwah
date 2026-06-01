<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Process an uploaded image, convert to WebP, and save to storage.
     *
     * @param UploadedFile|string $file The uploaded file or path
     * @param string $directory The directory to save the file
     * @param string $action The Intervention Image action ('cover', 'scaleDown', or 'none')
     * @param int|null $width The width for the action
     * @param int|null $height The height for the action (used for 'cover')
     * @param int $quality The quality of the WebP output
     * @param string|null $oldPath The path of the old image to delete
     * @return string The path to the saved image
     */
    public function upload(
        $file,
        string $directory,
        string $action = 'cover',
        ?int $width = 600,
        ?int $height = 600,
        int $quality = 80,
        ?string $oldPath = null
    ): string {
        $filename = Str::uuid() . '.webp';

        $image = is_string($file) ? Image::read($file) : Image::read($file->getRealPath());

        if ($action === 'cover') {
            $image->cover($width, $height ?? $width);
        } elseif ($action === 'scaleDown') {
            $image->scaleDown(width: $width);
        }

        $encoded = $image->toWebp($quality);

        $path = trim($directory, '/') . '/' . $filename;

        Storage::disk('public')->put($path, (string) $encoded);

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $path;
    }

    /**
     * Upload an image with multiple sizes (e.g., thumb and large).
     *
     * @param UploadedFile|string $file The uploaded file
     * @param string $baseDirectory The base directory
     * @param array $sizes Array of sizes, e.g., ['thumb' => ['width' => 400], 'large' => ['width' => 800]]
     * @param string|null $oldLargePath The path of the old large image to delete
     * @return array The paths to the saved images, keyed by size name
     */
    public function uploadMultipleSizes(
        $file,
        string $baseDirectory,
        array $sizes,
        ?string $oldLargePath = null
    ): array {
        $filename = Str::uuid() . '.webp';
        $paths = [];

        $sourceImage = is_string($file) ? Image::read($file) : Image::read($file->getRealPath());

        foreach ($sizes as $sizeName => $config) {
            $image = clone $sourceImage;

            $width = $config['width'] ?? null;
            $height = $config['height'] ?? null;
            $action = $config['action'] ?? 'scaleDown';
            $quality = $config['quality'] ?? 80;

            if ($action === 'cover') {
                $image->cover($width, $height ?? $width);
            } elseif ($action === 'scaleDown') {
                $image->scaleDown(width: $width);
            }

            $encoded = $image->toWebp($quality);

            $path = trim($baseDirectory, '/') . '/' . $sizeName . '/' . $filename;

            Storage::disk('public')->put($path, (string) $encoded);
            $paths[$sizeName] = $path;
        }

        if ($oldLargePath) {
            $this->deleteMultipleSizes($oldLargePath);
        }

        return $paths;
    }

    /**
     * Delete an image and its variations (thumb, large, etc.) based on the main path.
     * Assumes path format: directory/size/filename
     *
     * @param string|null $path The path of the main image (e.g., large)
     */
    public function deleteMultipleSizes(?string $path): void
    {
        if (!$path) return;

        // Delete the provided path (e.g., large)
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        // Try to guess and delete thumb if the path contains 'large'
        if (str_contains($path, '/large/')) {
            $thumbPath = str_replace('/large/', '/thumb/', $path);
            if (Storage::disk('public')->exists($thumbPath)) {
                Storage::disk('public')->delete($thumbPath);
            }
        }
    }

    /**
     * Delete an image from storage.
     *
     * @param string|null $path
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
