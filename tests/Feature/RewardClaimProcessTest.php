<?php

namespace Tests\Feature;

use App\Models\RewardClaim;
use App\Models\User;
use App\Notifications\RewardKlaimDibayar;
use App\Notifications\RewardKlaimDitolak;
use App\Services\RewardClaimService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RewardClaimProcessTest extends TestCase
{
    use RefreshDatabase;

    private RewardClaimService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);
        $this->service = new RewardClaimService;
    }

    private function pendingClaim(): RewardClaim
    {
        $user = User::factory()->create(['total_khidmah_points' => 600]);
        $user->assignRole('Kontributor');

        return $user->rewardClaims()->create([
            'amount' => 50000,
            'xp_at_claim' => 600,
            'ewallet_type' => 'OVO',
            'ewallet_number' => '081234567890',
            'ewallet_holder_name' => 'Abdullah',
            'status' => RewardClaim::STATUS_PENDING,
        ]);
    }

    public function test_mark_paid_mengisi_audit_bukti_dan_notifikasi(): void
    {
        Notification::fake();
        Storage::fake('local');
        $admin = User::factory()->create();
        $claim = $this->pendingClaim();

        $this->service->markPaid($claim, [
            'transferred_at' => '2026-06-27',
            'transfer_proof' => UploadedFile::fake()->image('bukti.jpg', 40, 40),
            'admin_note' => 'Sudah ditransfer',
            'processed_by' => $admin->id,
        ]);

        $claim->refresh();
        $this->assertSame(RewardClaim::STATUS_PAID, $claim->status);
        $this->assertSame($admin->id, $claim->processed_by);
        $this->assertNotNull($claim->processed_at);
        $this->assertSame('Sudah ditransfer', $claim->admin_note);
        Storage::disk('local')->assertExists($claim->transfer_proof_path);
        Notification::assertSentTo($claim->user, RewardKlaimDibayar::class);
    }

    public function test_reject_mengisi_alasan_dan_notifikasi(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $claim = $this->pendingClaim();

        $this->service->reject($claim, 'Nomor e-wallet tidak valid', $admin->id);

        $claim->refresh();
        $this->assertSame(RewardClaim::STATUS_REJECTED, $claim->status);
        $this->assertSame('Nomor e-wallet tidak valid', $claim->rejection_reason);
        $this->assertSame($admin->id, $claim->processed_by);
        Notification::assertSentTo($claim->user, RewardKlaimDitolak::class);
    }

    public function test_proses_pada_klaim_non_pending_diabaikan(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $claim = $this->pendingClaim();
        $claim->update(['status' => RewardClaim::STATUS_REJECTED]);

        $this->service->markPaid($claim, [
            'transferred_at' => '2026-06-27',
            'transfer_proof' => UploadedFile::fake()->image('bukti.jpg', 40, 40),
            'admin_note' => null,
            'processed_by' => $admin->id,
        ]);

        $claim->refresh();
        $this->assertSame(RewardClaim::STATUS_REJECTED, $claim->status);
        $this->assertNull($claim->transfer_proof_path);
        Notification::assertNothingSent();
    }
}
