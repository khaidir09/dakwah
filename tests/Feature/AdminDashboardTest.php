<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Super Admin');

        return $admin;
    }

    public function test_super_admin_can_view_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_non_admin_cannot_view_dashboard(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $this->assertNotEquals(200, $response->status());
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_renders_with_empty_database(): void
    {
        // Selain admin-nya sendiri tidak ada data sama sekali: memastikan tidak ada
        // division-by-zero pada delta dan empty state ter-render dengan aman.
        $this->actingAs($this->admin())
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Menunggu Moderasi')
            ->assertSee('Tidak ada kontribusi yang menunggu moderasi');
    }
}
