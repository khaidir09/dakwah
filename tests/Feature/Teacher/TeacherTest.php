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

    public function test_admin_can_create_teacher_with_source()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('public');

        $response = $this->post(route('guru.store'), [
            'name' => 'Test Teacher',
            'biografi' => 'Test Biography',
            'source' => 'Wikipedia',
            'foto' => UploadedFile::fake()->image('teacher.jpg'),
        ]);

        $response->assertRedirect(route('guru.index'));
        $this->assertDatabaseHas('teachers', [
            'name' => 'Test Teacher',
            'source' => 'Wikipedia',
        ]);
    }

    public function test_admin_can_update_teacher_with_source()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $teacher = Teacher::create([
            'name' => 'Old Name',
            'biografi' => 'Old Bio',
            'foto' => 'guru/old.jpg',
            'source' => 'Old Source',
        ]);

        $response = $this->put(route('guru.update', $teacher->id), [
            'name' => 'New Name',
            'biografi' => 'New Bio',
            'source' => 'New Source',
        ]);

        $response->assertRedirect(route('guru.index'));
        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'source' => 'New Source',
        ]);
    }

    public function test_source_is_nullable()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('guru.store'), [
            'name' => 'Teacher No Source',
            'biografi' => 'Bio',
            'foto' => UploadedFile::fake()->image('teacher.jpg'),
            // source is missing
        ]);

        $response->assertRedirect(route('guru.index'));
        $this->assertDatabaseHas('teachers', [
            'name' => 'Teacher No Source',
            'source' => null,
        ]);
    }
}
