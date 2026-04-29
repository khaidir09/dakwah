<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Assembly;
use App\Models\Teacher;
use App\Models\ScheduleNote;
use Livewire\Livewire;
use App\Livewire\User\KelolaCatatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;

class KelolaCatatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_kelola_catatan_page()
    {
        $this->withoutVite();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('kelola-catatan.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(KelolaCatatan::class);
    }

    public function test_user_can_view_their_notes_in_livewire_component()
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $province = Province::create(['code' => '11', 'name' => 'ACEH']);
        $city = City::create(['code' => '1101', 'province_code' => '11', 'name' => 'KABUPATEN SIMEULUE', 'api_myquran' => '1234']);

        $teacher = Teacher::create([
            'name' => 'Test Teacher',
            'domisili' => 'Domisili',
            'biografi' => 'bio',
            'foto' => 'foto.jpg'
        ]);

        $assembly = Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => 'Majelis 1',
            'deskripsi' => 'Deskripsi',
            'province_code' => '11',
            'city_code' => '1101',
            'status' => 'Aktif',
            'guru' => '-',
            'alamat' => 'Alamat',
            'maps' => 'Maps',
        ]);
        $schedule = Schedule::create([
            'assembly_id' => $assembly->id,
            'nama_jadwal' => 'Jadwal',
            'deskripsi' => 'Deskripsi',
            'hari' => 'Senin',
            'waktu' => '2023-01-01 08:00:00',
            'status' => 'Aktif',
        ]);

        $note1 = ScheduleNote::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'content' => 'My first note',
            'visibility' => 'Private',
            'status' => 'Approved',
        ]);

        $note2 = ScheduleNote::create([
            'user_id' => $otherUser->id,
            'schedule_id' => $schedule->id,
            'content' => 'Someone elses note',
            'visibility' => 'Public',
            'status' => 'Pending',
        ]);

        Livewire::actingAs($user)
            ->test(KelolaCatatan::class)
            ->assertSee('My first note')
            ->assertDontSee('Someone elses note');
    }

    public function test_user_can_create_a_note()
    {
        $this->withoutVite();

        $user = User::factory()->create();

        $province = Province::create(['code' => '11', 'name' => 'ACEH']);
        $city = City::create(['code' => '1101', 'province_code' => '11', 'name' => 'KABUPATEN SIMEULUE', 'api_myquran' => '1234']);

        $teacher = Teacher::create([
            'name' => 'Test Teacher',
            'domisili' => 'Domisili',
            'biografi' => 'bio',
            'foto' => 'foto.jpg'
        ]);

        $assembly = Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => 'Majelis 1',
            'deskripsi' => 'Deskripsi',
            'province_code' => '11',
            'city_code' => '1101',
            'status' => 'Aktif',
            'guru' => '-',
            'alamat' => 'Alamat',
            'maps' => 'Maps',
        ]);
        $schedule = Schedule::create([
            'assembly_id' => $assembly->id,
            'nama_jadwal' => 'Jadwal',
            'deskripsi' => 'Deskripsi',
            'hari' => 'Senin',
            'waktu' => '2023-01-01 08:00:00',
            'status' => 'Aktif',
        ]);

        Livewire::actingAs($user)
            ->test(KelolaCatatan::class)
            ->set('schedule_id', $schedule->id)
            ->set('content', 'New Test Note')
            ->set('visibility', 'Private')
            ->call('store')
            ->assertSee('Catatan berhasil ditambahkan.');

        $this->assertDatabaseHas('schedule_notes', [
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'content' => 'New Test Note',
            'visibility' => 'Private',
            'status' => 'Approved',
        ]);
    }

    public function test_user_can_update_a_note()
    {
        $this->withoutVite();

        $user = User::factory()->create();

        $province = Province::create(['code' => '11', 'name' => 'ACEH']);
        $city = City::create(['code' => '1101', 'province_code' => '11', 'name' => 'KABUPATEN SIMEULUE', 'api_myquran' => '1234']);

        $teacher = Teacher::create([
            'name' => 'Test Teacher',
            'domisili' => 'Domisili',
            'biografi' => 'bio',
            'foto' => 'foto.jpg'
        ]);

        $assembly = Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => 'Majelis 1',
            'deskripsi' => 'Deskripsi',
            'province_code' => '11',
            'city_code' => '1101',
            'status' => 'Aktif',
            'guru' => '-',
            'alamat' => 'Alamat',
            'maps' => 'Maps',
        ]);
        $schedule = Schedule::create([
            'assembly_id' => $assembly->id,
            'nama_jadwal' => 'Jadwal',
            'deskripsi' => 'Deskripsi',
            'hari' => 'Senin',
            'waktu' => '2023-01-01 08:00:00',
            'status' => 'Aktif',
        ]);

        $note = ScheduleNote::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'content' => 'Old Note',
            'visibility' => 'Private',
            'status' => 'Approved',
        ]);

        Livewire::actingAs($user)
            ->test(KelolaCatatan::class)
            ->call('edit', $note->id)
            ->set('content', 'Updated Note Content')
            ->set('visibility', 'Public')
            ->call('update')
            ->assertSee('Catatan berhasil diperbarui.');

        $this->assertDatabaseHas('schedule_notes', [
            'id' => $note->id,
            'content' => 'Updated Note Content',
            'visibility' => 'Public',
            'status' => 'Pending', // Because it went from Private -> Public
        ]);
    }

    public function test_user_can_delete_their_note()
    {
        $this->withoutVite();

        $user = User::factory()->create();

        $province = Province::create(['code' => '11', 'name' => 'ACEH']);
        $city = City::create(['code' => '1101', 'province_code' => '11', 'name' => 'KABUPATEN SIMEULUE', 'api_myquran' => '1234']);

        $teacher = Teacher::create([
            'name' => 'Test Teacher',
            'domisili' => 'Domisili',
            'biografi' => 'bio',
            'foto' => 'foto.jpg'
        ]);

        $assembly = Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => 'Majelis 1',
            'deskripsi' => 'Deskripsi',
            'province_code' => '11',
            'city_code' => '1101',
            'status' => 'Aktif',
            'guru' => '-',
            'alamat' => 'Alamat',
            'maps' => 'Maps',
        ]);
        $schedule = Schedule::create([
            'assembly_id' => $assembly->id,
            'nama_jadwal' => 'Jadwal',
            'deskripsi' => 'Deskripsi',
            'hari' => 'Senin',
            'waktu' => '2023-01-01 08:00:00',
            'status' => 'Aktif',
        ]);

        $note = ScheduleNote::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'content' => 'Note to delete',
            'visibility' => 'Private',
            'status' => 'Approved',
        ]);

        $this->assertDatabaseHas('schedule_notes', ['id' => $note->id]);

        Livewire::actingAs($user)
            ->test(KelolaCatatan::class)
            ->call('confirmDelete', $note->id)
            ->call('deleteNote')
            ->assertSee('Catatan berhasil dihapus.');

        $this->assertDatabaseMissing('schedule_notes', ['id' => $note->id]);
    }
}
