<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

trait HasImageUploads
{
    /**
     * Upload an image, process it, and optionally delete the old one.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param callable|null $processor Callback that receives and returns the Image instance
     * @param string|null $oldPath
     * @param string $disk
     * @return string
     */
    protected function uploadImage(
        UploadedFile $file,
        string $directory,
        ?callable $processor = null,
        ?string $oldPath = null,
        string $disk = 'public'
    ): string {
        $filename = Str::uuid() . '.webp';
        $path = trim($directory, '/') . '/' . $filename;

        $image = Image::read($file);

        if ($processor) {
            $image = $processor($image);
        }

        Storage::disk($disk)->put($path, (string) $image->toWebp(80));

        if ($oldPath && Storage::disk($disk)->exists($oldPath)) {
            Storage::disk($disk)->delete($oldPath);
        }

        return $path;
    }

    /**
     * Delete an image from storage.
     *
     * @param string|null $path
     * @param string $disk
     * @return void
     */
    protected function deleteImage(?string $path, string $disk = 'public'): void
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
