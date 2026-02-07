<?php

namespace Tests\Feature;

use App\Jobs\UploadLibraryToOpenNotebook;
use App\Models\Library;
use App\Models\User;
use App\Services\OpenNotebookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OpenNotebookIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_creation_dispatches_job()
    {
        Bus::fake();
        Storage::fake('public');

        // Create Admin
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $file = UploadedFile::fake()->create('book.pdf', 100);

        $response = $this->actingAs($admin)->post(route('libraries.store'), [
            'title' => 'Test Book',
            'category' => 'General',
            'description' => 'Test Description',
            'price_type' => 'free',
            'file' => $file,
        ]);

        $response->assertRedirect(route('libraries.index'));

        Bus::assertDispatched(UploadLibraryToOpenNotebook::class);
    }

    public function test_library_update_dispatches_job_on_file_change()
    {
        Bus::fake();
        Storage::fake('public');

        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $library = Library::withoutEvents(function () {
            return Library::create([
                'title' => 'Old Title',
                'slug' => 'old-title',
                'category' => 'General',
                'description' => 'Old Desc',
                'file_path' => 'old.pdf',
                'price_type' => 'free',
                'is_active' => true,
            ]);
        });

        $file = UploadedFile::fake()->create('new.pdf', 100);

        $response = $this->actingAs($admin)->put(route('libraries.update', $library), [
            'title' => 'New Title',
            'category' => 'General',
            'description' => 'Old Desc',
            'price_type' => 'free',
            'file' => $file,
        ]);

        $response->assertRedirect(route('libraries.index'));

        Bus::assertDispatched(UploadLibraryToOpenNotebook::class);
    }

    public function test_library_update_does_not_dispatch_job_without_file_change()
    {
        Bus::fake();
        Storage::fake('public');

        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $library = Library::withoutEvents(function () {
            return Library::create([
                'title' => 'Old Title',
                'slug' => 'old-title',
                'category' => 'General',
                'description' => 'Old Desc',
                'file_path' => 'old.pdf',
                'price_type' => 'free',
                'is_active' => true,
            ]);
        });

        $this->actingAs($admin)->put(route('libraries.update', $library), [
            'title' => 'New Title',
            'category' => 'General',
            'description' => 'Old Desc',
            'price_type' => 'free',
            // No file
        ]);

        Bus::assertNotDispatched(UploadLibraryToOpenNotebook::class);
    }

    public function test_service_uploads_file()
    {
        Http::fake();
        Storage::fake('public');

        Storage::disk('public')->put('libraries/files/test.pdf', 'dummy content');

        $library = new Library([
            'title' => 'Test Book Service',
            'category' => 'Science',
            'description' => 'A book about science',
        ]);
        $library->id = 123;
        $library->file_path = 'libraries/files/test.pdf';

        // Mock Config
        config(['services.open_notebook.base_url' => 'https://api.example.com']);
        config(['services.open_notebook.api_key' => 'secret_key']);
        config(['services.open_notebook.notebook_id' => 'nb_12345']);

        $service = new OpenNotebookService();
        $service->uploadLibrary($library);

        Http::assertSent(function ($request) {
            // Check multipart data
            $data = $request->data();
            $notebookId = null;
            $sourceId = null;

            foreach ($data as $item) {
                if ($item['name'] === 'notebook_id') {
                    $notebookId = $item['contents'];
                }
                if ($item['name'] === 'source_id') {
                    $sourceId = $item['contents'];
                }
            }

            return $request->url() == 'https://api.example.com/libraries' &&
                   $request->hasHeader('Authorization', 'Bearer secret_key') &&
                   $request->isMultipart() &&
                   $notebookId === 'nb_12345' &&
                   $sourceId === '123';
        });
    }
}
