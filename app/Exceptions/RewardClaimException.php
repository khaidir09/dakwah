<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat pengajuan klaim reward gagal memenuhi syarat (FR-02).
 * Pesan bersifat user-facing dan ditampilkan kembali oleh controller sebagai flash error.
 */
class RewardClaimException extends RuntimeException
{
    public static function programInactive(): self
    {
        return new self('Program reward sedang tidak aktif.');
    }

    public static function notKontributor(): self
    {
        return new self('Anda harus menjadi Kontributor untuk mengajukan klaim.');
    }

    public static function alreadyPaid(): self
    {
        return new self('Reward sudah pernah diterima.');
    }

    public static function pendingExists(): self
    {
        return new self('Sudah ada klaim yang sedang diproses.');
    }

    public static function insufficientXp(): self
    {
        return new self('XP belum mencukupi untuk klaim reward.');
    }
}
