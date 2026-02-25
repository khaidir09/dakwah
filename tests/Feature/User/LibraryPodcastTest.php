<?php

namespace Tests\Feature\User;

use App\Models\Library;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LibraryPodcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create Super Admin role if it doesn't exist
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    }

    public function test_admin_can_upload_library_with_episodes()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $file = UploadedFile::fake()->create('book.pdf', 100);
        $episode1 = UploadedFile::fake()->create('ep1.mp3', 1000);
        $episode2 = UploadedFile::fake()->create('ep2.mp3', 1000);

        $response = $this->actingAs($user)->post(route('libraries.store'), [
            'title' => 'Test Library',
            'category' => 'Fikih',
            'description' => 'Test Description',
            'price_type' => 'free',
            'file' => $file,
            'episodes' => [
                [
                    'title' => 'Episode 1',
                    'file' => $episode1,
                ],
                [
                    'title' => 'Episode 2',
                    'file' => $episode2,
                ],
            ]
        ]);

        $response->assertRedirect(route('libraries.index'));

        $this->assertDatabaseHas('libraries', ['title' => 'Test Library']);
        $library = Library::where('title', 'Test Library')->first();

        $this->assertCount(2, $library->episodes);
        $this->assertEquals('Episode 1', $library->episodes[0]->title);
        $this->assertEquals('Episode 2', $library->episodes[1]->title);

        Storage::disk('public')->assertExists($library->episodes[0]->file_path);
        Storage::disk('public')->assertExists($library->episodes[1]->file_path);
    }

    public function test_admin_can_add_new_episodes_on_update()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $library = Library::create([
            'title' => 'Old Library',
            'category' => 'Fikih',
            'description' => 'Desc',
            'price_type' => 'free',
            'slug' => 'old-library',
            'is_active' => true,
            'file_path' => 'libraries/files/dummy.pdf',
        ]);

        $episodeNew = UploadedFile::fake()->create('ep_new.mp3', 1000);

        $response = $this->actingAs($user)->put(route('libraries.update', $library), [
            'title' => 'Old Library',
            'category' => 'Fikih',
            'description' => 'Desc',
            'price_type' => 'free',
            'new_episodes' => [
                [
                    'title' => 'New Episode',
                    'file' => $episodeNew,
                ]
            ]
        ]);

        $response->assertRedirect(route('libraries.index'));
        $this->assertCount(1, $library->fresh()->episodes);
        $this->assertEquals('New Episode', $library->fresh()->episodes[0]->title);
    }
}
