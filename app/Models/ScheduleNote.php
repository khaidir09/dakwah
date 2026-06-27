<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleNote extends Model
{
    protected $guarded = [];

    public function contribution()
    {
        return $this->morphOne(Contribution::class, 'contributable');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('contribution_status')
                ->orWhere('contribution_status', 'approved');
        });
    }
}
