<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

trait HandlesImageUpload
{
    /**
     * Process and upload an image, converting it to WebP format.
     *
     * @param UploadedFile $file The uploaded image file.
     * @param string $directory The directory to store the image (e.g., 'events').
     * @param int|null $scaleDown The maximum width to scale down to.
     * @param int $quality The quality of the WebP image.
     * @param string|null $oldImagePath The path of the old image to delete.
     * @return string The path of the newly saved image.
     */
    protected function handleImageUpload(UploadedFile $file, string $directory, ?int $scaleDown = 800, int $quality = 80, ?string $oldImagePath = null): string
    {
        $filename = Str::uuid() . '.webp';
        $path = rtrim($directory, '/') . '/' . $filename;

        $image = Image::read($file);

        if ($scaleDown !== null) {
            $image->scaleDown($scaleDown);
        }

        $processedImage = $image->toWebp($quality);

        Storage::disk('public')->put($path, (string) $processedImage);

        if ($oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return $path;
    }
}
