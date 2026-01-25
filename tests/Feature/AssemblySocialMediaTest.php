<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Assembly;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssemblySocialMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_media_fields_can_be_added_during_update()
    {
        $this->withoutExceptionHandling();
        Storage::fake('public');

        // Create dependencies
        $user = User::factory()->create();
        $teacher = Teacher::create([
            'name' => 'Guru Test',
            'biografi' => 'Bio',
            'foto' => 'path/to/foto',
            'domisili' => 'Jakarta',
        ]);

        $assembly = Assembly::create([
            'user_id' => $user->id,
            'teacher_id' => $teacher->id,
            'nama_majelis' => 'Majelis Test',
            'deskripsi' => 'Deskripsi',
            'alamat' => 'Alamat',
            'guru' => 'Guru Dummy', // Legacy field
            'maps' => 'https://maps.google.com',
            'status' => 'Aktif',
            'province_code' => '11',
            'city_code' => '1101',
            'district_code' => '110101',
            'village_code' => '1101012001'
        ]);

        $response = $this->actingAs($user)
            ->put(route('kelola-majelis.update', $assembly->id), [
                'nama_majelis' => 'Majelis Updated',
                'alamat' => 'Alamat Baru',
                'deskripsi' => 'Deskripsi Baru',
                'youtube' => 'MyChannel',
                'instagram' => 'my.insta',
                'facebook' => 'https://facebook.com/page',
                'tiktok' => '@tiktok',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('assemblies', [
            'id' => $assembly->id,
            'youtube' => 'MyChannel',
            'instagram' => 'my.insta',
            'facebook' => 'https://facebook.com/page',
            'tiktok' => '@tiktok',
        ]);
    }
}
