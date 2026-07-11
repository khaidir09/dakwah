<?php

namespace Tests\Feature;

use App\Livewire\HomeJadwalMajelis;
use App\Livewire\ListJadwalMajelis;
use App\Models\Assembly;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ScheduleRecurrenceTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeacher(): Teacher
    {
        return Teacher::create([
            'name' => 'Guru Uji',
            'slug' => 'guru-uji',
            'domisili' => 'Banjarmasin',
            'biografi' => 'bio',
            'foto' => 'teachers/large/uji.webp',
        ]);
    }

    private function makeKontributor(): User
    {
        Role::findOrCreate('Kontributor', 'web');

        return tap(User::factory()->create())->assignRole('Kontributor');
    }

    private function makeAssembly(User $user): Assembly
    {
        Province::firstOrCreate(['code' => '63'], ['name' => 'KALIMANTAN SELATAN']);
        City::firstOrCreate(['code' => '6371'], ['province_code' => '63', 'name' => 'KOTA BANJARMASIN', 'api_myquran' => '1']);

        return Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => 'Majelis Uji',
            'deskripsi' => 'Deskripsi',
            'province_code' => '63',
            'city_code' => '6371',
            'status' => 'Aktif',
            'guru' => '-',
            'alamat' => 'Alamat',
            'maps' => 'Maps',
        ]);
    }

    private function baseSchedule(Assembly $assembly, Teacher $teacher, array $overrides = []): Schedule
    {
        return Schedule::create(array_merge([
            'nama_jadwal' => 'Jadwal Uji',
            'deskripsi' => 'Deskripsi jadwal',
            'assembly_id' => $assembly->id,
            'teacher_id' => $teacher->id,
            'waktu' => '2026-01-01 05:30:00',
            'hari' => 'Senin',
            'access' => 'Umum',
        ], $overrides));
    }

    public function test_schedule_defaults_to_weekly_gregorian(): void
    {
        $user = $this->makeKontributor();
        $schedule = $this->baseSchedule($this->makeAssembly($user), $this->makeTeacher());

        // Nilai default berasal dari kolom DB, jadi model perlu dimuat ulang.
        $schedule->refresh();

        $this->assertSame('weekly', $schedule->recurrence_type);
        $this->assertSame('gregorian', $schedule->calendar_system);
    }

    public function test_recurrence_label_for_each_type(): void
    {
        $user = $this->makeKontributor();
        $assembly = $this->makeAssembly($user);
        $teacher = $this->makeTeacher();

        $weekly = $this->baseSchedule($assembly, $teacher, ['hari' => 'Senin']);
        $monthlyWeekday = $this->baseSchedule($assembly, $teacher, ['recurrence_type' => 'monthly_weekday', 'hari' => 'Minggu', 'week_of_month' => '1']);
        $monthlyLast = $this->baseSchedule($assembly, $teacher, ['recurrence_type' => 'monthly_weekday', 'hari' => 'Jumat', 'week_of_month' => 'last']);
        $monthlyDate = $this->baseSchedule($assembly, $teacher, ['recurrence_type' => 'monthly_date', 'hari' => null, 'day_of_month' => 15]);
        $semimonthly = $this->baseSchedule($assembly, $teacher, ['recurrence_type' => 'semimonthly', 'hari' => 'Jumat', 'week_of_month' => '1', 'week_of_month_secondary' => '3']);
        $hijri = $this->baseSchedule($assembly, $teacher, ['recurrence_type' => 'hijri_first_week', 'calendar_system' => 'hijri', 'hari' => 'Kamis']);

        $this->assertSame('Setiap Senin', $weekly->recurrence_label);
        $this->assertSame('Minggu, pekan ke-1 tiap bulan', $monthlyWeekday->recurrence_label);
        $this->assertSame('Jumat, pekan terakhir tiap bulan', $monthlyLast->recurrence_label);
        $this->assertSame('Tiap tanggal 15', $monthlyDate->recurrence_label);
        $this->assertSame('Jumat, pekan ke-1 & pekan ke-3 tiap bulan', $semimonthly->recurrence_label);
        $this->assertSame('Kamis, pekan pertama tiap bulan Hijriah (1–7)', $hijri->recurrence_label);
    }

    public function test_home_widget_shows_weekly_but_not_berkala(): void
    {
        $this->withoutVite();

        $user = $this->makeKontributor();
        $assembly = $this->makeAssembly($user);
        $teacher = $this->makeTeacher();

        $map = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
        $today = $map[Carbon::now()->dayOfWeek];

        $this->baseSchedule($assembly, $teacher, ['nama_jadwal' => 'JADWAL-MINGGUAN-TAMPIL', 'hari' => $today]);
        $this->baseSchedule($assembly, $teacher, ['nama_jadwal' => 'JADWAL-BERKALA-SEMBUNYI', 'hari' => $today, 'recurrence_type' => 'monthly_weekday', 'week_of_month' => '1']);

        Livewire::test(HomeJadwalMajelis::class)
            ->assertSee('JADWAL-MINGGUAN-TAMPIL')
            ->assertDontSee('JADWAL-BERKALA-SEMBUNYI');
    }

    public function test_public_list_separates_berkala_and_hides_pending(): void
    {
        $this->withoutVite();

        $user = $this->makeKontributor();
        $assembly = $this->makeAssembly($user);
        $teacher = $this->makeTeacher();

        // Berkala yang sudah publik (tanpa status kontribusi).
        $this->baseSchedule($assembly, $teacher, ['nama_jadwal' => 'BERKALA-APPROVED', 'recurrence_type' => 'monthly_weekday', 'week_of_month' => '1']);
        // Berkala kontributor yang masih pending — tidak boleh tampil.
        $this->baseSchedule($assembly, $teacher, ['nama_jadwal' => 'BERKALA-PENDING', 'recurrence_type' => 'monthly_weekday', 'week_of_month' => '2', 'contribution_status' => 'pending', 'contributor_user_id' => $user->id]);

        Livewire::test(ListJadwalMajelis::class)
            ->assertSee('BERKALA-APPROVED')
            ->assertDontSee('BERKALA-PENDING');
    }

    public function test_contributor_can_submit_monthly_weekday_as_pending(): void
    {
        $user = $this->makeKontributor();
        $assembly = $this->makeAssembly($user);
        $teacher = $this->makeTeacher();

        $this->actingAs($user)->post(route('kontributor.jadwal.store'), [
            'nama_jadwal' => 'Kontribusi Bulanan',
            'assembly_id' => $assembly->id,
            'teacher_id' => $teacher->id,
            'waktu' => '05:30',
            'deskripsi' => 'Deskripsi',
            'access' => 'Umum',
            'recurrence_type' => 'monthly_weekday',
            'hari' => 'Minggu',
            'week_of_month' => '1',
        ])->assertRedirect(route('kontributor.saya'));

        $this->assertDatabaseHas('schedules', [
            'nama_jadwal' => 'Kontribusi Bulanan',
            'recurrence_type' => 'monthly_weekday',
            'week_of_month' => '1',
            'hari' => 'Minggu',
            'contribution_status' => 'pending',
            'contributor_user_id' => $user->id,
        ]);
    }

    public function test_contributor_monthly_date_nulls_hari(): void
    {
        $user = $this->makeKontributor();
        $assembly = $this->makeAssembly($user);
        $teacher = $this->makeTeacher();

        $this->actingAs($user)->post(route('kontributor.jadwal.store'), [
            'nama_jadwal' => 'Kontribusi Tanggal',
            'assembly_id' => $assembly->id,
            'teacher_id' => $teacher->id,
            'waktu' => '05:30',
            'deskripsi' => 'Deskripsi',
            'access' => 'Umum',
            'recurrence_type' => 'monthly_date',
            'day_of_month' => '15',
        ]);

        $schedule = Schedule::where('nama_jadwal', 'Kontribusi Tanggal')->first();
        $this->assertNotNull($schedule);
        $this->assertNull($schedule->hari);
        $this->assertSame(15, $schedule->day_of_month);
    }

    public function test_hijri_first_week_sets_calendar_system(): void
    {
        $user = $this->makeKontributor();
        $assembly = $this->makeAssembly($user);
        $teacher = $this->makeTeacher();

        $this->actingAs($user)->post(route('kontributor.jadwal.store'), [
            'nama_jadwal' => 'Kontribusi Hijriah',
            'assembly_id' => $assembly->id,
            'teacher_id' => $teacher->id,
            'waktu' => '19:00',
            'deskripsi' => 'Deskripsi',
            'access' => 'Umum',
            'recurrence_type' => 'hijri_first_week',
            'hari' => 'Kamis',
        ]);

        $this->assertDatabaseHas('schedules', [
            'nama_jadwal' => 'Kontribusi Hijriah',
            'recurrence_type' => 'hijri_first_week',
            'calendar_system' => 'hijri',
            'hari' => 'Kamis',
        ]);
    }

    public function test_validation_rejects_monthly_date_without_day(): void
    {
        $user = $this->makeKontributor();
        $assembly = $this->makeAssembly($user);
        $teacher = $this->makeTeacher();

        $this->actingAs($user)->post(route('kontributor.jadwal.store'), [
            'nama_jadwal' => 'Invalid Tanggal',
            'assembly_id' => $assembly->id,
            'teacher_id' => $teacher->id,
            'waktu' => '05:30',
            'deskripsi' => 'Deskripsi',
            'access' => 'Umum',
            'recurrence_type' => 'monthly_date',
        ])->assertSessionHasErrors('day_of_month');
    }

    public function test_validation_rejects_semimonthly_with_same_weeks(): void
    {
        $user = $this->makeKontributor();
        $assembly = $this->makeAssembly($user);
        $teacher = $this->makeTeacher();

        $this->actingAs($user)->post(route('kontributor.jadwal.store'), [
            'nama_jadwal' => 'Invalid Semimonthly',
            'assembly_id' => $assembly->id,
            'teacher_id' => $teacher->id,
            'waktu' => '05:30',
            'deskripsi' => 'Deskripsi',
            'access' => 'Umum',
            'recurrence_type' => 'semimonthly',
            'hari' => 'Jumat',
            'week_of_month' => '1',
            'week_of_month_secondary' => '1',
        ])->assertSessionHasErrors('week_of_month_secondary');
    }
}
