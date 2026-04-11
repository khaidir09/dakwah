<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleNote extends Model
{
    protected $guarded = [];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
