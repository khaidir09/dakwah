<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RamadhanSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'assembly_id',
        'hijri_year',
        'gregorian_start_date',
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'gregorian_start_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function lectures()
    {
        return $this->hasMany(RamadhanDailyLecture::class, 'ramadhan_schedule_id');
    }

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }
}
