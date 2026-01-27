<?php

namespace Tests\Feature;

use App\Models\Biography;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BiographyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create role if not exists
        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
    }

    public function test_admin_can_access_biographies_index()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $response = $this->actingAs($user)->get(route('biographies.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('biography');
    }

    public function test_admin_can_create_biography()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        Storage::fake('public');

        $response = $this->actingAs($user)->post(route('biographies.store'), [
            'nama' => 'Wali Test',
            'deskripsi' => 'Deskripsi Wali Test',
            'maps' => 'https://maps.google.com',
            'tanggal_wafat_masehi' => '2024-01-01',
            'tanggal_wafat_hijriah' => '1 Rajab 1445 H',
            'foto' => UploadedFile::fake()->image('wali.jpg'),
        ]);

        $response->assertRedirect(route('biographies.index'));

        $this->assertDatabaseHas('biographies', [
            'nama' => 'Wali Test',
            'deskripsi' => 'Deskripsi Wali Test',
        ]);

        $biography = Biography::first();
        $this->assertNotNull($biography->slug);
        $this->assertNotNull($biography->foto);
    }

    public function test_admin_can_create_biography_with_sources()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $sources = [
            ['name' => 'Wikipedia', 'url' => 'https://wikipedia.org'],
            ['name' => 'Kitab', 'url' => ''],
        ];

        $response = $this->actingAs($user)->post(route('biographies.store'), [
            'nama' => 'Wali Source',
            'deskripsi' => 'Bio with sources',
            'source' => $sources,
        ]);

        $response->assertRedirect(route('biographies.index'));

        $biography = Biography::where('nama', 'Wali Source')->first();
        $this->assertIsArray($biography->source);
        $this->assertCount(2, $biography->source);
        $this->assertEquals('Wikipedia', $biography->source[0]['name']);
    }
}
