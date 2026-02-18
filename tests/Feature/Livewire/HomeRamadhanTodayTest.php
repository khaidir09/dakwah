<?php

namespace Tests\Feature\Livewire;

use App\Livewire\HomeRamadhanToday;
use App\Models\Assembly;
use App\Models\RamadhanDailyLecture;
use App\Models\RamadhanSchedule;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeRamadhanTodayTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_lectures_for_today()
    {
        // 1. Create Assembly
        $assembly = Assembly::create([
            'nama_majelis' => 'Majelis Test',
            'deskripsi' => 'Desc',
            'guru' => 'Guru',
            'alamat' => 'Address',
            'maps' => 'Maps',
            'status' => 'Aktif',
            'province_code' => 11,
            'city_code' => 1101,
            'district_code' => 110101,
            'village_code' => 1101012001,
        ]);

        // 2. Create Teacher
        $teacher = Teacher::create([
            'name' => 'Ustadz Test',
            'slug' => 'ustadz-test',
            'biografi' => 'Biography content',
            'foto' => 'teachers/test.jpg',
            'domisili' => 'Domisili Test',
        ]);

        // 3. Create Schedule starting 4 days ago (so today is Day 5)
        $startDate = Carbon::today()->subDays(4);

        $schedule = RamadhanSchedule::create([
            'assembly_id' => $assembly->id,
            'hijri_year' => 1447,
            'gregorian_start_date' => $startDate,
            'title' => 'Jadwal Ramadhan',
            'is_active' => true,
        ]);

        // 4. Create Lecture for Today (Day 5)
        RamadhanDailyLecture::create([
            'ramadhan_schedule_id' => $schedule->id,
            'day' => 5,
            'teacher_id' => $teacher->id,
            'title' => 'Topic Today',
            'time' => '17:00',
        ]);

        // 5. Create Lecture for Tomorrow (Day 6)
        RamadhanDailyLecture::create([
            'ramadhan_schedule_id' => $schedule->id,
            'day' => 6,
            'teacher_id' => $teacher->id,
            'title' => 'Topic Tomorrow',
            'time' => '17:00',
        ]);

        // 6. Test Component
        Livewire::test(HomeRamadhanToday::class)
            ->assertSee('Jadwal Ramadhan Hari Ini')
            ->assertSee('Majelis Test')
            ->assertSee('Ustadz Test')
            ->assertSee('Topic Today')
            ->assertDontSee('Topic Tomorrow');
    }

    /** @test */
    public function it_handles_custom_speaker()
    {
        $assembly = Assembly::create([
            'nama_majelis' => 'Majelis Custom',
            'deskripsi' => 'Desc',
            'guru' => 'Guru',
            'alamat' => 'Address',
            'maps' => 'Maps',
            'status' => 'Aktif',
            'province_code' => 11,
            'city_code' => 1101,
            'district_code' => 110101,
            'village_code' => 1101012001,
        ]);

        // Schedule starts today (Day 1)
        $schedule = RamadhanSchedule::create([
            'assembly_id' => $assembly->id,
            'hijri_year' => 1447,
            'gregorian_start_date' => Carbon::today(),
            'title' => 'Jadwal Ramadhan Custom',
            'is_active' => true,
        ]);

        RamadhanDailyLecture::create([
            'ramadhan_schedule_id' => $schedule->id,
            'day' => 1,
            'custom_speaker_name' => 'Custom Ustadz',
            'title' => 'Custom Topic',
            'time' => '18:00',
        ]);

        Livewire::test(HomeRamadhanToday::class)
            ->assertSee('Custom Ustadz')
            ->assertSee('Custom Topic');
    }

    /** @test */
    public function it_does_not_show_inactive_schedules()
    {
        $assembly = Assembly::create([
            'nama_majelis' => 'Majelis Inactive',
            'deskripsi' => 'Desc',
            'guru' => 'Guru',
            'alamat' => 'Address',
            'maps' => 'Maps',
            'status' => 'Aktif',
            'province_code' => 11,
            'city_code' => 1101,
            'district_code' => 110101,
            'village_code' => 1101012001,
        ]);

        $schedule = RamadhanSchedule::create([
            'assembly_id' => $assembly->id,
            'hijri_year' => 1447,
            'gregorian_start_date' => Carbon::today(),
            'title' => 'Jadwal Ramadhan Inactive',
            'is_active' => false, // Inactive
        ]);

        RamadhanDailyLecture::create([
            'ramadhan_schedule_id' => $schedule->id,
            'day' => 1,
            'custom_speaker_name' => 'Inactive Speaker',
            'title' => 'Inactive Topic',
            'time' => '18:00',
        ]);

        Livewire::test(HomeRamadhanToday::class)
            ->assertDontSee('Inactive Speaker');
    }
}
