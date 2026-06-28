<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardSetting extends Model
{
    protected $fillable = ['amount', 'min_xp', 'is_active'];

    protected $casts = [
        'amount' => 'integer',
        'min_xp' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Konfigurasi reward bersifat single-row. Mengembalikan baris konfigurasi,
     * membuat baris default (Rp 50.000, threshold 501, aktif) bila belum ada.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'amount' => 50000,
            'min_xp' => 501,
            'is_active' => true,
        ]);
    }
}
