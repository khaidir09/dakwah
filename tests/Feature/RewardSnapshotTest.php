<?php

namespace Tests\Feature;

use App\Models\RewardClaim;
use App\Models\User;
use App\Notifications\RewardKlaimDibayar;
use App\Services\RewardClaimService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RewardSnapshotTest extends TestCase
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

    public function test_penurunan_xp_setelah_klaim_tidak_membatalkan_klaim(): void
    {
        Notification::fake();
        Storage::fake('local');

        $user = $this->kontributor(510);
        $claim = $this->service->submit($user, [
            'ewallet_type' => 'GoPay',
            'ewallet_number' => '081234567890',
            'ewallet_holder_name' => 'Abdullah',
        ]);

        // Simulasikan revoke: XP turun di bawah threshold setelah klaim masuk.
        $user->update(['total_khidmah_points' => 480]);

        $claim->refresh();
        $this->assertSame(RewardClaim::STATUS_PENDING, $claim->status);
        $this->assertSame(510, $claim->xp_at_claim); // snapshot tidak berubah

        // Admin tetap dapat memproses klaim.
        $this->service->markPaid($claim, [
            'transferred_at' => now()->toDateString(),
            'transfer_proof' => UploadedFile::fake()->image('bukti.jpg', 40, 40),
            'admin_note' => 'Transfer via GoPay',
            'processed_by' => $user->id,
        ]);

        $claim->refresh();
        $this->assertSame(RewardClaim::STATUS_PAID, $claim->status);
        $this->assertNotNull($claim->transfer_proof_path);
        Storage::disk('local')->assertExists($claim->transfer_proof_path);
        Notification::assertSentTo($user, RewardKlaimDibayar::class);
    }
}
