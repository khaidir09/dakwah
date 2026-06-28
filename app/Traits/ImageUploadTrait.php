<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

trait ImageUploadTrait
{
    /**
     * Handle image upload, resize/crop, convert to WebP, and save to storage.
     *
     * @param  \Illuminate\Http\UploadedFile  $file  The uploaded file.
     * @param  string  $folder  The destination folder inside the target disk.
     * @param  int|null  $width  The target width.
     * @param  int|null  $height  The target height.
     * @param  int  $quality  The WebP compression quality.
     * @param  string  $disk  The storage disk to write to (default 'public').
     * @return string The stored file path.
     */
    protected function handleImageUpload($file, $folder, $width = null, $height = null, $quality = 80, $disk = 'public')
    {
        $filename = Str::uuid().'.webp';

        $image = Image::read($file);

        if ($width && $height) {
            $image->cover($width, $height);
        } elseif ($width) {
            $image->scaleDown(width: $width);
        } elseif ($height) {
            $image->scaleDown(height: $height);
        }

        $encodedImage = $image->toWebp($quality);

        Storage::disk($disk)->put($folder.'/'.$filename, (string) $encodedImage);

        return $folder.'/'.$filename;
    }

    /**
     * Delete an existing image from the public storage disk.
     *
     * @param  string|null  $path  The path of the image to delete.
     * @param  string  $disk  The storage disk to delete from (default 'public').
     * @return void
     */
    protected function deleteImage(?string $path, $disk = 'public')
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
