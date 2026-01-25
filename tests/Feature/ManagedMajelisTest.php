<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Assembly;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagedMajelisTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_loads_add_schedule_page()
    {
        $user = User::factory()->create();
        $assembly = Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => 'Majelis Test',
            'deskripsi' => 'Desc',
            'alamat' => 'Alamat',
            'guru' => 'Guru Name',
            'maps' => 'https://maps.google.com',
            'status' => 'Aktif',
        ]);

        $this->actingAs($user)
            ->get(route('kelola-jadwal-majelis.create'))
            ->assertStatus(200);
    }
}
