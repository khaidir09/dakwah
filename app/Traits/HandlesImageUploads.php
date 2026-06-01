<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

trait HandlesImageUploads
{
    /**
     * Handle image upload and generate a thumbnail and a large version.
     *
     * @param UploadedFile $file The uploaded file.
     * @param string $basePath The storage base path (e.g. 'majelis').
     * @param int $thumbWidth The width for the thumbnail.
     * @param int $largeWidth The width for the large image.
     * @param int $quality WebP quality.
     * @return array Returns an array with 'thumb' and 'large' paths.
     */
    protected function uploadImageWithThumbnail(UploadedFile $file, string $basePath, int $thumbWidth = 400, int $largeWidth = 800, int $quality = 80): array
    {
        $filename = Str::uuid() . '.webp';

        // 1. Create Thumbnail Version
        $thumb = Image::read($file)
            ->scaleDown(width: $thumbWidth)
            ->toWebp($quality);

        // 2. Create Large Version
        $large = Image::read($file)
            ->scaleDown(width: $largeWidth)
            ->toWebp($quality);

        $thumbPath = rtrim($basePath, '/') . '/thumb/' . $filename;
        $largePath = rtrim($basePath, '/') . '/large/' . $filename;

        // 3. Save to Storage
        Storage::disk('public')->put($thumbPath, (string) $thumb);
        Storage::disk('public')->put($largePath, (string) $large);

        return [
            'thumb' => $thumbPath,
            'large' => $largePath,
        ];
    }

    /**
     * Delete an image with its thumbnail given the large image path.
     *
     * @param string $largePath
     * @return void
     */
    protected function deleteImageWithThumbnail(string $largePath): void
    {
        if (Storage::disk('public')->exists($largePath)) {
            Storage::disk('public')->delete($largePath);
        }

        $thumbPath = str_replace('/large/', '/thumb/', $largePath);

        if (Storage::disk('public')->exists($thumbPath)) {
            Storage::disk('public')->delete($thumbPath);
        }
    }
}
