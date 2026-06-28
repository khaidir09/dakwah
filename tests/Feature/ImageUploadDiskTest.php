<?php

namespace Tests\Feature;

use App\Traits\ImageUploadTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadDiskTest extends TestCase
{
    private function uploader(): object
    {
        return new class
        {
            use ImageUploadTrait;

            public function upload($file, $folder, $disk = 'public'): string
            {
                return $this->handleImageUpload($file, $folder, null, null, 80, $disk);
            }

            public function remove(?string $path, $disk = 'public'): void
            {
                $this->deleteImage($path, $disk);
            }
        };
    }

    public function test_dapat_menyimpan_ke_disk_privat_local(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $path = $this->uploader()->upload(
            UploadedFile::fake()->image('proof.jpg', 50, 50),
            'reward-proofs',
            'local',
        );

        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
        $this->assertStringEndsWith('.webp', $path);
    }

    public function test_default_disk_tetap_public(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $path = $this->uploader()->upload(
            UploadedFile::fake()->image('cover.jpg', 50, 50),
            'libraries',
        );

        Storage::disk('public')->assertExists($path);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_delete_image_menghapus_dari_disk_yang_ditentukan(): void
    {
        Storage::fake('local');

        $uploader = $this->uploader();
        $path = $uploader->upload(
            UploadedFile::fake()->image('proof.jpg', 50, 50),
            'reward-proofs',
            'local',
        );
        Storage::disk('local')->assertExists($path);

        $uploader->remove($path, 'local');
        Storage::disk('local')->assertMissing($path);
    }
}
