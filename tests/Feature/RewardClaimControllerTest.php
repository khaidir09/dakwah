<?php

namespace Tests\Feature;

use App\Models\RewardClaim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RewardClaimControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);
    }

    private function kontributor(int $xp = 600): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'total_khidmah_points' => $xp,
        ]);
        $user->assignRole('Kontributor');

        return $user;
    }

    private function ewalletData(): array
    {
        return [
            'ewallet_type' => 'Dana',
            'ewallet_number' => '081234567890',
            'ewallet_holder_name' => 'Abdullah',
        ];
    }

    public function test_kontributor_eligible_melihat_kartu_klaim(): void
    {
        $this->actingAs($this->kontributor())
            ->get(route('kontributor.saya'))
            ->assertOk()
            ->assertSee('Klaim Reward');
    }

    public function test_kontributor_dengan_xp_kurang_tidak_melihat_kartu_klaim(): void
    {
        $this->actingAs($this->kontributor(300))
            ->get(route('kontributor.saya'))
            ->assertOk()
            ->assertDontSee('Ajukan Klaim');
    }

    public function test_submit_klaim_berhasil(): void
    {
        Notification::fake();
        $user = $this->kontributor();

        $this->actingAs($user)
            ->post(route('kontributor.reward.store'), $this->ewalletData())
            ->assertRedirect(route('kontributor.saya'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reward_claims', [
            'user_id' => $user->id,
            'status' => RewardClaim::STATUS_PENDING,
            'amount' => 50000,
        ]);
    }

    public function test_anti_duplikat_via_http(): void
    {
        $user = $this->kontributor();

        $this->actingAs($user)->post(route('kontributor.reward.store'), $this->ewalletData());
        $this->actingAs($user)
            ->post(route('kontributor.reward.store'), $this->ewalletData())
            ->assertRedirect(route('kontributor.saya'))
            ->assertSessionHas('error');

        $this->assertSame(1, $user->rewardClaims()->count());
    }

    public function test_validasi_gagal_saat_field_kosong(): void
    {
        $user = $this->kontributor();

        $this->actingAs($user)
            ->post(route('kontributor.reward.store'), [
                'ewallet_type' => 'Dana',
                'ewallet_holder_name' => 'Abdullah',
            ])
            ->assertSessionHasErrors('ewallet_number');

        $this->assertSame(0, $user->rewardClaims()->count());
    }

    public function test_non_kontributor_tidak_bisa_submit(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('kontributor.reward.store'), $this->ewalletData())
            ->assertForbidden();
    }

    public function test_dashboard_menampilkan_status_pending_lalu_paid(): void
    {
        $user = $this->kontributor();
        $claim = $user->rewardClaims()->create([
            'amount' => 50000,
            'xp_at_claim' => 600,
            'ewallet_type' => 'Dana',
            'ewallet_number' => '081234567890',
            'ewallet_holder_name' => 'Abdullah',
            'status' => RewardClaim::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->get(route('kontributor.saya'))
            ->assertOk()
            ->assertSee('Sedang Diproses');

        $claim->update([
            'status' => RewardClaim::STATUS_PAID,
            'transferred_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('kontributor.saya'))
            ->assertOk()
            ->assertSee('Reward Sudah Diterima');
    }
}
