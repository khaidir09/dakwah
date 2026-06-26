<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KontribusiXpSetting extends Model
{
    protected $table = 'kontribusi_xp_settings';

    protected $fillable = ['contribution_type', 'points', 'label'];

    public static function pointsFor(string $type): int
    {
        return static::where('contribution_type', $type)->value('points') ?? 0;
    }
}
