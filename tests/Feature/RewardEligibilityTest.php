<?php

namespace Tests\Feature;

use App\Models\RewardClaim;
use App\Models\RewardSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RewardEligibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);
    }

    private function kontributor(int $xp): User
    {
        $user = User::factory()->create(['total_khidmah_points' => $xp]);
        $user->assignRole('Kontributor');

        return $user;
    }

    public function test_eligible_saat_semua_syarat_terpenuhi(): void
    {
        $this->assertTrue($this->kontributor(501)->eligibleForReward());
    }

    public function test_tidak_eligible_saat_xp_di_bawah_threshold(): void
    {
        $this->assertFalse($this->kontributor(500)->eligibleForReward());
    }

    public function test_tidak_eligible_saat_program_nonaktif(): void
    {
        RewardSetting::current()->update(['is_active' => false]);

        $this->assertFalse($this->kontributor(600)->eligibleForReward());
    }

    public function test_tidak_eligible_saat_bukan_kontributor(): void
    {
        $user = User::factory()->create(['total_khidmah_points' => 600]);

        $this->assertFalse($user->eligibleForReward());
    }

    public function test_tidak_eligible_saat_sudah_punya_klaim_paid(): void
    {
        $user = $this->kontributor(600);
        $user->rewardClaims()->create([
            'amount' => 50000,
            'xp_at_claim' => 600,
            'ewallet_type' => 'Dana',
            'ewallet_number' => '0810000',
            'ewallet_holder_name' => 'Abdullah',
            'status' => RewardClaim::STATUS_PAID,
        ]);

        $this->assertFalse($user->eligibleForReward());
    }

    public function test_tidak_eligible_saat_ada_klaim_pending(): void
    {
        $user = $this->kontributor(600);
        $user->rewardClaims()->create([
            'amount' => 50000,
            'xp_at_claim' => 600,
            'ewallet_type' => 'Dana',
            'ewallet_number' => '0810000',
            'ewallet_holder_name' => 'Abdullah',
            'status' => RewardClaim::STATUS_PENDING,
        ]);

        $this->assertFalse($user->eligibleForReward());
    }

    public function test_eligible_kembali_setelah_klaim_ditolak(): void
    {
        $user = $this->kontributor(600);
        $user->rewardClaims()->create([
            'amount' => 50000,
            'xp_at_claim' => 600,
            'ewallet_type' => 'Dana',
            'ewallet_number' => '0810000',
            'ewallet_holder_name' => 'Abdullah',
            'status' => RewardClaim::STATUS_REJECTED,
        ]);

        $this->assertTrue($user->eligibleForReward());
    }
}
