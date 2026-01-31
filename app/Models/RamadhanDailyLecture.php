<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class RamadhanDailyLecture extends Model
{
    use HasFactory;

    protected $fillable = [
        'ramadhan_schedule_id',
        'day',
        'teacher_id',
        'custom_speaker_name',
        'title',
        'time',
    ];

    public function schedule()
    {
        return $this->belongsTo(RamadhanSchedule::class, 'ramadhan_schedule_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->schedule?->gregorian_start_date ? $this->schedule->gregorian_start_date->copy()->addDays($this->day - 1) : null,
        );
    }
}
