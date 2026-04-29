<?php

namespace Tests\Feature\User;

use App\Models\Assembly;
use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalMajelisControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_jadwal_majelis_detail_shows_correct_data()
    {
        $this->withoutVite();

        $teacher = Teacher::create([
            'name' => 'Ustadz Test',
            'biografi' => 'Test',
            'foto' => 'test.jpg',
            'domisili' => '1101'
        ]);

        $assembly = Assembly::create([
            'nama_majelis' => 'Majelis Test',
            'deskripsi' => 'Test',
            'province_code' => '11',
            'status' => 'Aktif',
            'guru' => '-',
            'alamat' => 'Test',
            'maps' => 'Test',
            'teacher_id' => $teacher->id
        ]);

        $schedule = Schedule::create([
            'assembly_id' => $assembly->id,
            'hari' => 'Senin',
            'waktu' => '20:00',
            'nama_jadwal' => 'Kajian Rutin',
            'deskripsi' => 'Kajian Kitab',
            'status' => 'Aktif'
        ]);

        $response = $this->get(route('jadwal-majelis-detail', $schedule->id));

        $response->assertStatus(200);
        $response->assertSee('Kajian Rutin');
        $response->assertSee('-');
        $response->assertSee('Senin');
    }
}
