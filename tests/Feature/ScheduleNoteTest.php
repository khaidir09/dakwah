<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Contribution;
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


    public function test_public_note_requires_minimum_150_characters()
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
            'content' => 'Catatan terlalu pendek.',
            'visibility' => 'Public',
        ]);

        $response->assertSessionHasErrors('content');
        $this->assertDatabaseMissing('schedule_notes', ['user_id' => $user->id, 'schedule_id' => $schedule->id]);
    }

    public function test_public_note_creates_contribution_record()
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

        $content = str_repeat('Isi catatan pengajian yang bermakna dan informatif. ', 5);

        $response = $this->actingAs($user)->post(route('jadwal-majelis.notes.store', $schedule->id), [
            'content' => $content,
            'visibility' => 'Public',
        ]);

        $response->assertRedirect();

        $note = ScheduleNote::where('user_id', $user->id)->first();
        $this->assertNotNull($note);
        $this->assertEquals('pending', $note->contribution_status);

        $this->assertDatabaseHas('contributions', [
            'user_id' => $user->id,
            'contributable_id' => $note->id,
            'contributable_type' => ScheduleNote::class,
        ]);
    }

    public function test_private_note_does_not_require_minimum_characters()
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
            'content' => 'Catatan pendek pribadi.',
            'visibility' => 'Private',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_user_can_edit_public_note_created_by_another_user()
    {
        $this->withoutVite();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

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

        $note = ScheduleNote::create([
            'user_id' => $user1->id,
            'schedule_id' => $schedule->id,
            'content' => 'Original public note content',
            'visibility' => 'Public',
            'status' => 'Approved',
        ]);

        $response = $this->actingAs($user2)->put(route('jadwal-majelis.notes.update', $note->id), [
            'content' => 'Updated public note content by user 2',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('schedule_notes', [
            'id' => $note->id,
            'content' => 'Updated public note content by user 2',
        ]);
    }

    public function test_user_cannot_edit_private_note_created_by_another_user()
    {
        $this->withoutVite();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

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

        $note = ScheduleNote::create([
            'user_id' => $user1->id,
            'schedule_id' => $schedule->id,
            'content' => 'Original private note content',
            'visibility' => 'Private',
            'status' => 'Approved',
        ]);

        $response = $this->actingAs($user2)->put(route('jadwal-majelis.notes.update', $note->id), [
            'content' => 'Updated private note content by user 2',
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('schedule_notes', [
            'id' => $note->id,
            'content' => 'Original private note content',
        ]);
    }

}
