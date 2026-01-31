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
            // Add other required fields if necessary
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

    public function test_lecture_dates_are_calculated_correctly()
    {
        $schedule = RamadhanSchedule::create([
            'hijri_year' => 1446,
            'gregorian_start_date' => '2025-03-01',
            'title' => 'Test Schedule',
            'is_active' => true,
        ]);

        $lecture = RamadhanDailyLecture::create([
            'ramadhan_schedule_id' => $schedule->id,
            'day' => 1,
            'title' => 'First Day',
            'time' => '04:30:00',
        ]);

        // Reload to ensure relationship can be fetched
        $lecture = $lecture->fresh(['schedule']);

        $this->assertNotNull($lecture->schedule, 'Schedule relationship is null');
        $this->assertEquals('2025-03-01', $lecture->date->format('Y-m-d'));
    }
}
