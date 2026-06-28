<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardClaim extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'amount',
        'xp_at_claim',
        'ewallet_type',
        'ewallet_number',
        'ewallet_holder_name',
        'status',
        'rejection_reason',
        'admin_note',
        'transfer_proof_path',
        'transferred_at',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'xp_at_claim' => 'integer',
        'transferred_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
