<?php

namespace Tests\Feature;

use App\Models\RewardClaim;
use App\Models\User;
use App\Notifications\RewardKlaimDibayar;
use App\Notifications\RewardKlaimDitolak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RewardClaimAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Super Admin');

        return $admin;
    }

    private function pendingClaim(): RewardClaim
    {
        $user = User::factory()->create(['total_khidmah_points' => 600]);
        $user->assignRole('Kontributor');

        return $user->rewardClaims()->create([
            'amount' => 50000,
            'xp_at_claim' => 600,
            'ewallet_type' => 'Dana',
            'ewallet_number' => '081234567890',
            'ewallet_holder_name' => 'Abdullah',
            'status' => RewardClaim::STATUS_PENDING,
        ]);
    }

    public function test_admin_dapat_membuka_daftar_klaim(): void
    {
        $claim = $this->pendingClaim();

        $this->actingAs($this->admin())
            ->get(route('admin.reward-klaim.index'))
            ->assertOk()
            ->assertSee($claim->user->name)
            ->assertSee('Klaim Reward Kontributor');
    }

    public function test_non_admin_tidak_dapat_membuka_daftar_klaim(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('admin.reward-klaim.index'))
            ->assertForbidden();
    }

    public function test_admin_menandai_klaim_paid(): void
    {
        Notification::fake();
        Storage::fake('local');
        $admin = $this->admin();
        $claim = $this->pendingClaim();

        $this->actingAs($admin)
            ->put(route('admin.reward-klaim.paid', $claim), [
                'transferred_at' => '2026-06-27',
                'transfer_proof' => UploadedFile::fake()->image('bukti.jpg', 40, 40),
                'admin_note' => 'Transfer berhasil',
            ])
            ->assertRedirect(route('admin.reward-klaim.index'))
            ->assertSessionHas('message');

        $claim->refresh();
        $this->assertSame(RewardClaim::STATUS_PAID, $claim->status);
        $this->assertSame($admin->id, $claim->processed_by);
        $this->assertNotNull($claim->transfer_proof_path);
        Storage::disk('local')->assertExists($claim->transfer_proof_path);
        Notification::assertSentTo($claim->user, RewardKlaimDibayar::class);
    }

    public function test_mark_paid_butuh_bukti_transfer(): void
    {
        $claim = $this->pendingClaim();

        $this->actingAs($this->admin())
            ->put(route('admin.reward-klaim.paid', $claim), [
                'transferred_at' => '2026-06-27',
            ])
            ->assertSessionHasErrors('transfer_proof');

        $this->assertSame(RewardClaim::STATUS_PENDING, $claim->fresh()->status);
    }

    public function test_admin_menolak_klaim(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $claim = $this->pendingClaim();

        $this->actingAs($admin)
            ->put(route('admin.reward-klaim.reject', $claim), [
                'rejection_reason' => 'Nomor e-wallet tidak valid',
            ])
            ->assertRedirect(route('admin.reward-klaim.index'))
            ->assertSessionHas('message');

        $claim->refresh();
        $this->assertSame(RewardClaim::STATUS_REJECTED, $claim->status);
        $this->assertSame('Nomor e-wallet tidak valid', $claim->rejection_reason);
        Notification::assertSentTo($claim->user, RewardKlaimDitolak::class);
    }

    public function test_reject_butuh_alasan(): void
    {
        $claim = $this->pendingClaim();

        $this->actingAs($this->admin())
            ->put(route('admin.reward-klaim.reject', $claim), [])
            ->assertSessionHasErrors('rejection_reason');

        $this->assertSame(RewardClaim::STATUS_PENDING, $claim->fresh()->status);
    }

    public function test_filter_status_menyaring_daftar(): void
    {
        $pending = $this->pendingClaim();
        $rejected = $this->pendingClaim();
        $rejected->update(['status' => RewardClaim::STATUS_REJECTED]);

        $this->actingAs($this->admin())
            ->get(route('admin.reward-klaim.index', ['status' => 'rejected']))
            ->assertOk()
            ->assertSee($rejected->user->name)
            ->assertDontSee($pending->user->name);
    }
}
