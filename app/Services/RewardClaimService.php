<?php

namespace App\Services;

use App\Exceptions\RewardClaimException;
use App\Models\RewardClaim;
use App\Models\RewardSetting;
use App\Models\User;
use App\Notifications\RewardKlaimDibayar;
use App\Notifications\RewardKlaimDiterima;
use App\Notifications\RewardKlaimDitolak;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\DB;

class RewardClaimService
{
    use ImageUploadTrait;

    /**
     * Ajukan klaim reward untuk kontributor. Memvalidasi ulang seluruh syarat FR-02
     * (defense in depth), snapshot nominal & XP, lalu kirim notifikasi konfirmasi.
     *
     * @param  array{ewallet_type:string,ewallet_number:string,ewallet_holder_name:string}  $data
     *
     * @throws RewardClaimException bila syarat tidak terpenuhi.
     */
    public function submit(User $user, array $data): RewardClaim
    {
        return DB::transaction(function () use ($user, $data) {
            $setting = RewardSetting::current();

            if (! $setting->is_active) {
                throw RewardClaimException::programInactive();
            }

            if (! $user->hasRole('Kontributor')) {
                throw RewardClaimException::notKontributor();
            }

            // Kunci baris klaim user untuk mencegah race double-pending / double-paid.
            $claims = $user->rewardClaims()->lockForUpdate()->get();

            if ($claims->contains('status', RewardClaim::STATUS_PAID)) {
                throw RewardClaimException::alreadyPaid();
            }

            if ($claims->contains('status', RewardClaim::STATUS_PENDING)) {
                throw RewardClaimException::pendingExists();
            }

            if ($user->total_khidmah_points < $setting->min_xp) {
                throw RewardClaimException::insufficientXp();
            }

            $claim = $user->rewardClaims()->create([
                'amount' => $setting->amount,
                'xp_at_claim' => $user->total_khidmah_points,
                'ewallet_type' => $data['ewallet_type'],
                'ewallet_number' => $data['ewallet_number'],
                'ewallet_holder_name' => $data['ewallet_holder_name'],
                'status' => RewardClaim::STATUS_PENDING,
            ]);

            $user->notify(new RewardKlaimDiterima($claim->amount));

            return $claim;
        });
    }

    /**
     * Tandai klaim sebagai sudah ditransfer (paid). Hanya klaim pending yang diproses.
     * Bukti transfer disimpan di disk privat 'local'.
     *
     * @param  array{transferred_at:mixed,transfer_proof:\Illuminate\Http\UploadedFile,admin_note:?string,processed_by:int}  $data
     */
    public function markPaid(RewardClaim $claim, array $data): void
    {
        DB::transaction(function () use ($claim, $data) {
            $claim->refresh();

            if ($claim->status !== RewardClaim::STATUS_PENDING) {
                return;
            }

            $proofPath = $this->handleImageUpload($data['transfer_proof'], 'reward-proofs', null, null, 80, 'local');

            $claim->update([
                'status' => RewardClaim::STATUS_PAID,
                'transferred_at' => $data['transferred_at'],
                'admin_note' => $data['admin_note'] ?? null,
                'transfer_proof_path' => $proofPath,
                'processed_by' => $data['processed_by'],
                'processed_at' => now(),
            ]);

            $claim->user?->notify(new RewardKlaimDibayar($claim->amount, $claim->transferred_at));
        });
    }

    /**
     * Tolak klaim dengan alasan. Hanya klaim pending yang diproses.
     */
    public function reject(RewardClaim $claim, string $reason, int $processedBy): void
    {
        DB::transaction(function () use ($claim, $reason, $processedBy) {
            $claim->refresh();

            if ($claim->status !== RewardClaim::STATUS_PENDING) {
                return;
            }

            $claim->update([
                'status' => RewardClaim::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'processed_by' => $processedBy,
                'processed_at' => now(),
            ]);

            $claim->user?->notify(new RewardKlaimDitolak($claim->amount, $reason));
        });
    }
}
