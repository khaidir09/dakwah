<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\ScheduleNote;
use App\Models\Schedule;
use App\Models\Assembly;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use App\Models\Teacher;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\User\CatatanPengajianList;

class CatatanPengajianListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles are available for Spatie permissions if needed
        Role::firstOrCreate(['name' => 'User']);
    }

    public function test_can_view_catatan_pengajian_page()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get('/catatan-pengajian')
             ->assertStatus(200);
    }

    public function test_livewire_component_renders_top_users_and_notes()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CatatanPengajianList::class)
            ->assertStatus(200);
    }

    public function test_can_view_catatan_pengajian_detail()
    {
        $user = User::factory()->create();

        Province::firstOrCreate(['code' => '11'], ['name' => 'ACEH']);
        $city = City::firstOrCreate(
            ['code' => '1101'],
            ['province_code' => '11', 'name' => 'KABUPATEN TEST']
        );
        $teacher = Teacher::create([
            'name' => 'Teacher Test',
            'domisili' => 'Domisili',
            'biografi' => 'Biografi',
            'foto' => 'foto.jpg',
        ]);
        $assembly = Assembly::create([
            'nama_majelis' => 'Majelis Test',
            'deskripsi' => 'Deskripsi',
            'province_code' => '11',
            'city_code' => $city->code,
            'status' => 'Aktif',
            'guru' => '-',
            'teacher_id' => $teacher->id,
            'alamat' => 'Alamat',
            'maps' => 'Maps',
        ]);

        $schedule = Schedule::create([
            'nama_jadwal' => 'Jadwal Test',
            'deskripsi' => 'Deskripsi',
            'assembly_id' => $assembly->id,
            'waktu' => now()->addDays(1),
        ]);

        $note = ScheduleNote::create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'content' => 'Ini adalah catatan pengajian test yang sangat panjang. Ini adalah catatan pengajian test yang sangat panjang. Ini adalah catatan pengajian test yang sangat panjang.',
            'visibility' => 'Public',
            'status' => 'Approved',
        ]);

        $this->actingAs($user)
             ->get("/catatan-pengajian/{$note->id}")
             ->assertStatus(200)
             ->assertSee('Ini adalah catatan pengajian test')
             ->assertSee('Majelis Test')
             ->assertSee('Jadwal Test');
    }

    public function test_cannot_view_private_or_unapproved_catatan_pengajian_detail()
    {
        $user = User::factory()->create();

        Province::firstOrCreate(['code' => '11'], ['name' => 'ACEH']);
        $city = City::firstOrCreate(
            ['code' => '1101'],
            ['province_code' => '11', 'name' => 'KABUPATEN TEST']
        );
        $teacher = Teacher::create([
            'name' => 'Teacher Test',
            'domisili' => 'Domisili',
            'biografi' => 'Biografi',
            'foto' => 'foto.jpg',
        ]);
        $assembly = Assembly::create([
            'nama_majelis' => 'Majelis Test',
            'deskripsi' => 'Deskripsi',
            'province_code' => '11',
            'city_code' => $city->code,
            'status' => 'Aktif',
            'guru' => '-',
            'teacher_id' => $teacher->id,
            'alamat' => 'Alamat',
            'maps' => 'Maps',
        ]);

        $schedule = Schedule::create([
            'nama_jadwal' => 'Jadwal Test',
            'deskripsi' => 'Deskripsi',
            'assembly_id' => $assembly->id,
            'waktu' => now()->addDays(1),
        ]);

        $privateNote = ScheduleNote::create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'content' => 'Ini catatan private',
            'visibility' => 'Private',
            'status' => 'Approved',
        ]);

        $unapprovedNote = ScheduleNote::create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'content' => 'Ini catatan unapproved',
            'visibility' => 'Public',
            'status' => 'Pending',
        ]);

        $this->actingAs($user)
             ->get("/catatan-pengajian/{$privateNote->id}")
             ->assertStatus(404);

        $this->actingAs($user)
             ->get("/catatan-pengajian/{$unapprovedNote->id}")
             ->assertStatus(404);
    }
}
