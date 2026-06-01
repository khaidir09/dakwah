<?php

namespace App\Traits;

use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ImageUploadTrait
{
    /**
     * Handle image upload, resize/crop, convert to WebP, and save to storage.
     *
     * @param \Illuminate\Http\UploadedFile $file The uploaded file.
     * @param string $folder The destination folder inside the 'public' disk.
     * @param int|null $width The target width.
     * @param int|null $height The target height.
     * @param int $quality The WebP compression quality.
     * @return string The stored file path.
     */
    protected function handleImageUpload($file, $folder, $width = null, $height = null, $quality = 80)
    {
        $filename = Str::uuid() . '.webp';

        $image = Image::read($file);

        if ($width && $height) {
            $image->cover($width, $height);
        } elseif ($width) {
            $image->scaleDown(width: $width);
        } elseif ($height) {
            $image->scaleDown(height: $height);
        }

        $encodedImage = $image->toWebp($quality);

        Storage::disk('public')->put($folder . '/' . $filename, (string) $encodedImage);

        return $folder . '/' . $filename;
    }

    /**
     * Delete an existing image from the public storage disk.
     *
     * @param string|null $path The path of the image to delete.
     * @return void
     */
    protected function deleteImage(?string $path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
