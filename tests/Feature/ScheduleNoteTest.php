<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Assembly;
use App\Models\ScheduleNote;
use Spatie\Permission\Models\Role;

class ScheduleNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_schedule_detail()
    {
        $this->withoutVite();

        $assembly = Assembly::create([
            'nama_majelis' => 'Test Majelis',
            'deskripsi' => 'test',
            'province_code' => '62',
            'status' => 'Aktif',
            'guru' => '-',
            'alamat' => 'test',
            'maps' => 'test',
        ]);

        $schedule = Schedule::create([
            'nama_jadwal' => 'Test Jadwal',
            'deskripsi' => 'Test desk',
            'assembly_id' => $assembly->id,
            'waktu' => now(),
            'status' => 'Aktif',
            'hari' => 'Senin',
        ]);

        $response = $this->get(route('jadwal-majelis-detail', $schedule->id));

        $response->assertStatus(200);
        $response->assertSee('Test Jadwal');
    }

    public function test_user_can_add_private_note()
    {
        $this->withoutVite();

        $user = User::factory()->create();

        $assembly = Assembly::create([
            'nama_majelis' => 'Test Majelis',
            'deskripsi' => 'test',
            'province_code' => '62',
            'status' => 'Aktif',
            'guru' => '-',
            'alamat' => 'test',
            'maps' => 'test',
        ]);

        $schedule = Schedule::create([
            'nama_jadwal' => 'Test Jadwal',
            'deskripsi' => 'Test desk',
            'assembly_id' => $assembly->id,
            'waktu' => now(),
            'status' => 'Aktif',
            'hari' => 'Senin',
        ]);

        $response = $this->actingAs($user)->post(route('jadwal-majelis.notes.store', $schedule->id), [
            'content' => 'My secret note',
            'visibility' => 'Private',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('schedule_notes', [
            'content' => 'My secret note',
            'visibility' => 'Private',
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
        ]);
    }
}
