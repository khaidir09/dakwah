<?php

namespace Tests\Feature;

use App\Exceptions\RewardClaimException;
use App\Models\RewardClaim;
use App\Models\RewardSetting;
use App\Models\User;
use App\Notifications\RewardKlaimDiterima;
use App\Services\RewardClaimService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RewardClaimSubmitTest extends TestCase
{
    use RefreshDatabase;

    private RewardClaimService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);
        $this->service = new RewardClaimService;
    }

    private function kontributor(int $xp): User
    {
        $user = User::factory()->create(['total_khidmah_points' => $xp]);
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

    public function test_submit_membuat_klaim_pending_dengan_snapshot_dan_notifikasi(): void
    {
        Notification::fake();
        $user = $this->kontributor(520);

        $claim = $this->service->submit($user, $this->ewalletData());

        $this->assertSame(RewardClaim::STATUS_PENDING, $claim->status);
        $this->assertSame(50000, $claim->amount);     // snapshot reward_settings.amount
        $this->assertSame(520, $claim->xp_at_claim);  // snapshot XP saat klaim
        $this->assertSame('Dana', $claim->ewallet_type);
        $this->assertDatabaseHas('reward_claims', [
            'user_id' => $user->id,
            'status' => RewardClaim::STATUS_PENDING,
            'amount' => 50000,
        ]);
        Notification::assertSentTo($user, RewardKlaimDiterima::class);
    }

    public function test_submit_memakai_nominal_terbaru_dari_pengaturan(): void
    {
        RewardSetting::current()->update(['amount' => 75000, 'min_xp' => 600]);
        $user = $this->kontributor(650);

        $claim = $this->service->submit($user, $this->ewalletData());

        $this->assertSame(75000, $claim->amount);
        $this->assertSame(650, $claim->xp_at_claim);
    }

    public function test_submit_menolak_saat_xp_kurang(): void
    {
        $user = $this->kontributor(300);

        $this->expectException(RewardClaimException::class);
        $this->expectExceptionMessage('XP belum mencukupi');

        $this->service->submit($user, $this->ewalletData());
    }

    public function test_submit_menolak_klaim_pending_ganda(): void
    {
        $user = $this->kontributor(600);
        $this->service->submit($user, $this->ewalletData());

        $this->expectException(RewardClaimException::class);
        $this->expectExceptionMessage('sedang diproses');

        $this->service->submit($user, $this->ewalletData());
        $this->assertSame(1, $user->rewardClaims()->count());
    }

    public function test_submit_menolak_saat_sudah_pernah_paid(): void
    {
        $user = $this->kontributor(600);
        $user->rewardClaims()->create([
            'amount' => 50000,
            'xp_at_claim' => 600,
            'ewallet_type' => 'Dana',
            'ewallet_number' => '0810',
            'ewallet_holder_name' => 'Abdullah',
            'status' => RewardClaim::STATUS_PAID,
        ]);

        $this->expectException(RewardClaimException::class);
        $this->expectExceptionMessage('sudah pernah diterima');

        $this->service->submit($user, $this->ewalletData());
    }

    public function test_submit_menolak_saat_program_nonaktif(): void
    {
        RewardSetting::current()->update(['is_active' => false]);
        $user = $this->kontributor(600);

        $this->expectException(RewardClaimException::class);
        $this->expectExceptionMessage('tidak aktif');

        $this->service->submit($user, $this->ewalletData());
    }

    public function test_submit_menolak_saat_bukan_kontributor(): void
    {
        $user = User::factory()->create(['total_khidmah_points' => 600]);

        $this->expectException(RewardClaimException::class);
        $this->expectExceptionMessage('harus menjadi Kontributor');

        $this->service->submit($user, $this->ewalletData());
    }
}
