<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryPayout extends Model
{
    protected $fillable = [
        'library_id',
        'period',
        'purchase_count',
        'gross_amount',
        'share_percent',
        'author_amount',
        'payee_name',
        'transferred_at',
        'transfer_proof_path',
        'admin_note',
        'processed_by',
    ];

    protected $casts = [
        'purchase_count' => 'integer',
        'gross_amount' => 'integer',
        'share_percent' => 'integer',
        'author_amount' => 'integer',
        'transferred_at' => 'date',
    ];

    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
