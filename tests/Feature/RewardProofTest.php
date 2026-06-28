<?php

namespace Tests\Feature;

use App\Models\RewardClaim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RewardProofTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);
    }

    private function paidClaimWithProof(): RewardClaim
    {
        Storage::fake('local');

        $owner = User::factory()->create(['email_verified_at' => now()]);
        $owner->assignRole('Kontributor');

        $path = 'reward-proofs/'.Str::uuid().'.webp';
        Storage::disk('local')->put($path, 'fake-bytes');

        return $owner->rewardClaims()->create([
            'amount' => 50000,
            'xp_at_claim' => 600,
            'ewallet_type' => 'Dana',
            'ewallet_number' => '081234567890',
            'ewallet_holder_name' => 'Abdullah',
            'status' => RewardClaim::STATUS_PAID,
            'transferred_at' => now(),
            'transfer_proof_path' => $path,
        ]);
    }

    public function test_pemilik_dapat_melihat_bukti(): void
    {
        $claim = $this->paidClaimWithProof();

        $this->actingAs($claim->user)
            ->get(route('reward-klaim.bukti', $claim))
            ->assertOk();
    }

    public function test_admin_dapat_melihat_bukti(): void
    {
        $claim = $this->paidClaimWithProof();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('reward-klaim.bukti', $claim))
            ->assertOk();
    }

    public function test_pengguna_lain_dilarang_melihat_bukti(): void
    {
        $claim = $this->paidClaimWithProof();
        $other = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($other)
            ->get(route('reward-klaim.bukti', $claim))
            ->assertForbidden();
    }

    public function test_klaim_tanpa_bukti_mengembalikan_404(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $owner->assignRole('Kontributor');
        $claim = $owner->rewardClaims()->create([
            'amount' => 50000,
            'xp_at_claim' => 600,
            'ewallet_type' => 'Dana',
            'ewallet_number' => '081234567890',
            'ewallet_holder_name' => 'Abdullah',
            'status' => RewardClaim::STATUS_PENDING,
        ]);

        $this->actingAs($owner)
            ->get(route('reward-klaim.bukti', $claim))
            ->assertNotFound();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $claim = $this->paidClaimWithProof();

        $this->get(route('reward-klaim.bukti', $claim))
            ->assertRedirect(route('login'));
    }
}
