<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Assembly;
use App\Models\RamadhanSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RamadhanTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_ramadhan_schedule_list()
    {
        $user = User::factory()->create();

        // Create Assembly with required fields based on migration
        $assembly = Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => 'Test Assembly',
            'deskripsi' => 'Test Description',
            'guru' => 'Test Teacher',
            'alamat' => 'Test Address',
            'maps' => 'https://maps.google.com',
            'status' => 'Aktif',
        ]);

        RamadhanSchedule::create([
            'assembly_id' => $assembly->id,
            'hijri_year' => 1447,
            'gregorian_start_date' => now()->toDateString(),
            'title' => 'Test Schedule',
            'is_active' => true,
        ]);

        $response = $this->get(route('ramadhan-list'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.user.ramadhan.index');
        $response->assertSee('Test Assembly');
        $response->assertSee('Test Schedule');
    }

    public function test_can_view_ramadhan_schedule_detail()
    {
        $user = User::factory()->create();

        $assembly = Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => 'Test Assembly',
            'deskripsi' => 'Test Description',
            'guru' => 'Test Teacher',
            'alamat' => 'Test Address',
            'maps' => 'https://maps.google.com',
            'status' => 'Aktif',
        ]);

        $schedule = RamadhanSchedule::create([
            'assembly_id' => $assembly->id,
            'hijri_year' => 1447,
            'gregorian_start_date' => now()->toDateString(),
            'title' => 'Test Schedule',
            'is_active' => true,
        ]);

        $response = $this->get(route('ramadhan-detail', $schedule->id));

        $response->assertStatus(200);
        $response->assertViewIs('pages.user.ramadhan.detail');
        $response->assertSee('Test Schedule');
    }
}
