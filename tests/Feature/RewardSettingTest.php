<?php

namespace Tests\Feature;

use App\Models\RewardSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RewardSettingTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Super Admin');

        return $admin;
    }

    public function test_current_membuat_baris_default_saat_belum_ada(): void
    {
        $this->assertDatabaseCount('reward_settings', 0);

        $setting = RewardSetting::current();

        $this->assertSame(50000, $setting->amount);
        $this->assertSame(501, $setting->min_xp);
        $this->assertTrue($setting->is_active);
        $this->assertDatabaseCount('reward_settings', 1);
    }

    public function test_current_idempoten_tidak_membuat_baris_duplikat(): void
    {
        $first = RewardSetting::current();
        $second = RewardSetting::current();

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('reward_settings', 1);
    }

    public function test_current_mengembalikan_baris_yang_sudah_ada_tanpa_menimpa(): void
    {
        $existing = RewardSetting::create([
            'amount' => 75000,
            'min_xp' => 600,
            'is_active' => false,
        ]);

        $setting = RewardSetting::current();

        $this->assertTrue($existing->is($setting));
        $this->assertSame(75000, $setting->amount);
        $this->assertSame(600, $setting->min_xp);
        $this->assertFalse($setting->is_active);
        $this->assertDatabaseCount('reward_settings', 1);
    }

    public function test_admin_dapat_memperbarui_pengaturan_reward(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->put(route('admin.reward-settings.update'), [
                'amount' => 75000,
                'min_xp' => 600,
                'is_active' => '0',
            ])
            ->assertRedirect(route('admin.reward-settings.index'));

        $setting = RewardSetting::current();
        $this->assertSame(75000, $setting->amount);
        $this->assertSame(600, $setting->min_xp);
        $this->assertFalse($setting->is_active);
    }

    public function test_admin_dapat_membuka_halaman_pengaturan_reward(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.reward-settings.index'))
            ->assertOk()
            ->assertSee('Pengaturan Reward Kontributor');
    }

    public function test_validasi_menolak_input_tidak_valid(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->put(route('admin.reward-settings.update'), [
                'amount' => -100,
                'min_xp' => 0,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors(['amount', 'min_xp']);
    }

    public function test_non_admin_tidak_dapat_mengakses_pengaturan_reward(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('admin.reward-settings.index'))
            ->assertForbidden();
    }
}
