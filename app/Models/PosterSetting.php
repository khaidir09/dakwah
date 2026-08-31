<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosterSetting extends Model
{
    protected $fillable = ['monthly_quota', 'is_active'];

    protected $casts = [
        'monthly_quota' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Konfigurasi poster AI bersifat single-row. Mengembalikan baris konfigurasi,
     * membuat baris default (5 poster/bulan, aktif) bila belum ada.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'monthly_quota' => 5,
            'is_active' => true,
        ]);
    }
}
