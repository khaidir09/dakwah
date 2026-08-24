<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiClient extends Model
{
    protected $guarded = [];

    protected $hidden = ['key_hash'];

    protected $casts = [
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
        'rate_limit_per_minute' => 'integer',
    ];

    /**
     * Panjang prefix yang disimpan terpisah agar lookup key tidak perlu
     * memindai seluruh tabel dan membuka hash satu per satu.
     */
    public const PREFIX_LENGTH = 8;

    public const KEY_LENGTH = 40;

    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }
}
