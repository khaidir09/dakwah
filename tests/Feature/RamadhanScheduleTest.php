<?php

namespace Tests\Feature;

use App\Models\RamadhanSchedule;
use App\Models\RamadhanDailyLecture;
use App\Models\Assembly;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RamadhanScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_schedule_with_assembly()
    {
        $user = User::factory()->create();
        $assembly = Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => 'Masjid Raya',
            'deskripsi' => 'Deskripsi',
            'guru' => 'Guru',
            'alamat' => 'Alamat',
            'maps' => 'Maps',
            'province_code' => '11',
            'city_code' => '1101',
            'district_code' => '1101010',
            'village_code' => '1101010001',
        ]);

        $schedule = RamadhanSchedule::create([
            'assembly_id' => $assembly->id,
            'hijri_year' => 1446,
            'gregorian_start_date' => '2025-03-01',
            'title' => 'Test Schedule',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('ramadhan_schedules', [
            'hijri_year' => 1446,
            'assembly_id' => $assembly->id,
        ]);

        $this->assertEquals($assembly->id, $schedule->assembly->id);
        $this->assertTrue($assembly->ramadhanSchedules->contains($schedule));
    }

    public function test_user_cannot_create_schedule_if_active_exists()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $assembly = Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => 'Masjid Raya',
            'deskripsi' => 'Deskripsi',
            'guru' => 'Guru',
            'alamat' => 'Alamat',
            'maps' => 'Maps',
            'province_code' => '11',
            'city_code' => '1101',
            'district_code' => '1101010',
            'village_code' => '1101010001',
        ]);

        // Create an active schedule
        RamadhanSchedule::create([
            'assembly_id' => $assembly->id,
            'hijri_year' => 1446,
            'gregorian_start_date' => '2025-03-01',
            'title' => 'Active Schedule',
            'is_active' => true,
        ]);

        // Access Index Page (Should have warning)
        $response = $this->get(route('kelola-ramadhan.index'));
        $response->assertStatus(200);
        $response->assertSee('Anda masih memiliki jadwal aktif');
        $response->assertViewHas('hasActiveSchedule', true);

        // Try to access Create Page (Should redirect)
        $response = $this->get(route('kelola-ramadhan.create'));
        $response->assertRedirect(route('kelola-ramadhan.index'));
        $response->assertSessionHas('error', 'Anda masih memiliki jadwal aktif.');
    }
}
