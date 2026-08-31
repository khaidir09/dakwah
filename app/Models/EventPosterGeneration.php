<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPosterGeneration extends Model
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'event_id',
        'assembly_id',
        'style',
        'prompt',
        'model',
        'image_path',
        'status',
        'error_message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class);
    }

    /**
     * Dasar perhitungan kuota: hanya generate yang berhasil yang dihitung, karena
     * hanya itu yang menghasilkan gambar. Batas bulan mengikuti timezone aplikasi
     * (Asia/Makassar), bukan UTC.
     */
    public function scopeSuccessThisMonth($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->where('status', self::STATUS_SUCCESS)
            ->where('created_at', '>=', Carbon::now()->startOfMonth());
    }

    public static function quotaUsedThisMonth(int $userId): int
    {
        return static::query()->successThisMonth($userId)->count();
    }
}
