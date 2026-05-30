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

    public function test_user_can_comment_on_public_note()
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
            'content' => 'Public note content',
            'visibility' => 'Public',
            'status' => 'Approved',
        ]);

        $response = $this->actingAs($user2)->post(route('jadwal-majelis.notes.comments.store', $note->id), [
            'content' => 'This is a comment',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('schedule_note_comments', [
            'schedule_note_id' => $note->id,
            'user_id' => $user2->id,
            'content' => 'This is a comment',
        ]);
    }

    public function test_user_cannot_comment_on_private_note()
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
            'content' => 'Private note content',
            'visibility' => 'Private',
            'status' => 'Approved',
        ]);

        $response = $this->actingAs($user2)->post(route('jadwal-majelis.notes.comments.store', $note->id), [
            'content' => 'This is a comment',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_comment()
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

        $note = ScheduleNote::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'content' => 'Public note content',
            'visibility' => 'Public',
            'status' => 'Approved',
        ]);

        $comment = \App\Models\ScheduleNoteComment::create([
            'schedule_note_id' => $note->id,
            'user_id' => $user->id,
            'content' => 'My comment',
        ]);

        $response = $this->actingAs($user)->delete(route('jadwal-majelis.notes.comments.destroy', $comment->id));

        $response->assertRedirect();

        $this->assertDatabaseMissing('schedule_note_comments', [
            'id' => $comment->id,
        ]);
    }
}
