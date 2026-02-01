<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Library;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_library()
    {
        Storage::fake('public');

        // Ensure roles table exists and create role
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $file = UploadedFile::fake()->create('book.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('libraries.store'), [
            'title' => 'Test Book',
            'category' => 'Fikih',
            'price_type' => 'free',
            'description' => 'Test Description',
            'file' => $file,
        ]);

        $response->assertRedirect(route('libraries.index'));

        $this->assertDatabaseHas('libraries', [
            'title' => 'Test Book',
            'category' => 'Fikih',
        ]);

        $library = Library::first();
        Storage::disk('public')->assertExists($library->file_path);
    }

    public function test_public_can_view_library()
    {
        $library = Library::create([
            'title' => 'Public Book',
            'slug' => 'public-book',
            'category' => 'Sejarah',
            'description' => 'Desc',
            'file_path' => 'path/to/file.pdf',
            'price_type' => 'free',
            'is_active' => true,
        ]);

        $response = $this->get(route('pustaka-list'));
        $response->assertStatus(200);
        $response->assertSee('Public Book');

        $responseDetail = $this->get(route('pustaka-detail', $library->slug));
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee('Public Book');
    }
}
