<?php

namespace Tests\Feature\Teacher;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_teacher_with_multiple_sources()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('public');

        $sources = [
            ['name' => 'Source 1', 'url' => 'http://source1.com'],
            ['name' => 'Source 2', 'url' => 'http://source2.com'],
        ];

        $response = $this->post(route('guru.store'), [
            'name' => 'Test Teacher',
            'biografi' => 'Test Biography',
            'source' => $sources,
            'foto' => UploadedFile::fake()->image('teacher.jpg'),
        ]);

        $response->assertRedirect(route('guru.index'));

        // Assert stored as JSON/Array
        $teacher = Teacher::where('name', 'Test Teacher')->first();
        $this->assertIsArray($teacher->source);
        $this->assertCount(2, $teacher->source);
        $this->assertEquals('Source 1', $teacher->source[0]['name']);
    }

    public function test_admin_can_update_teacher_with_sources()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $teacher = Teacher::create([
            'name' => 'Old Name',
            'biografi' => 'Old Bio',
            'foto' => 'guru/old.jpg',
            'source' => [['name' => 'Old Source', 'url' => '']],
        ]);

        $newSources = [
            ['name' => 'New Source', 'url' => 'http://new.com'],
        ];

        $response = $this->put(route('guru.update', $teacher->id), [
            'name' => 'New Name',
            'biografi' => 'New Bio',
            'source' => $newSources,
        ]);

        $response->assertRedirect(route('guru.index'));

        $teacher->refresh();
        $this->assertEquals('New Name', $teacher->name);
        $this->assertCount(1, $teacher->source);
        $this->assertEquals('New Source', $teacher->source[0]['name']);
    }

    public function test_source_is_nullable()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('guru.store'), [
            'name' => 'Teacher No Source',
            'biografi' => 'Bio',
            'foto' => UploadedFile::fake()->image('teacher.jpg'),
            // source missing or empty
        ]);

        $response->assertRedirect(route('guru.index'));
        $this->assertDatabaseHas('teachers', [
            'name' => 'Teacher No Source',
            'source' => null,
        ]);
    }
}
