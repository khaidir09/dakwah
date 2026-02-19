<?php

namespace Tests\Feature;

use App\Models\Assembly;
use App\Models\Teacher;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssemblyLeaderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::create(['name' => 'Super Admin']);
    }

    public function test_can_create_assembly_with_custom_leader_name_only()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        // Simulate browser: teacher_id disabled (missing), custom_leader_name present
        $response = $this->post(route('majelis.store'), [
            'nama_majelis' => 'Majelis Manual',
            'deskripsi' => 'Deskripsi',
            'alamat' => 'Alamat',
            'custom_leader_name' => 'Manual Leader',
            'maps' => 'https://maps.google.com',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('assemblies', [
            'nama_majelis' => 'Majelis Manual',
            'custom_leader_name' => 'Manual Leader',
            'teacher_id' => null,
        ]);
    }

    public function test_can_create_assembly_with_teacher_only()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Super Admin');
        $teacher = Teacher::create([
            'name' => 'Guru Test',
            'slug' => 'guru-test',
            'biografi' => 'Bio',
            'foto' => 'foto.jpg',
            'domisili' => 'Domisili',
        ]);
        $this->actingAs($user);

        // Simulate browser: teacher_id present, custom_leader_name disabled (missing)
        $response = $this->post(route('majelis.store'), [
            'nama_majelis' => 'Majelis Teacher',
            'deskripsi' => 'Deskripsi',
            'alamat' => 'Alamat',
            'teacher_id' => $teacher->id,
            'maps' => 'https://maps.google.com',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('assemblies', [
            'nama_majelis' => 'Majelis Teacher',
            'teacher_id' => $teacher->id,
            'custom_leader_name' => null,
        ]);
    }

    public function test_cannot_create_assembly_without_any_leader()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        // Simulate browser: both disabled or empty?
        // If I send empty strings:
        $response = $this->post(route('majelis.store'), [
            'nama_majelis' => 'Majelis Invalid',
            'deskripsi' => 'Deskripsi',
            'alamat' => 'Alamat',
            'maps' => 'https://maps.google.com',
            // Missing both leader fields
        ]);

        $response->assertSessionHasErrors(['teacher_id', 'custom_leader_name']);
    }
}
