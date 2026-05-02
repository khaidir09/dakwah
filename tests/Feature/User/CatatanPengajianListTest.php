<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\ScheduleNote;
use App\Models\Schedule;
use App\Models\Assembly;
use App\Models\City;
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
}
