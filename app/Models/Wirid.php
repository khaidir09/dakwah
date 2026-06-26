<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wirid extends Model
{
    protected $guarded = [];

    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'wirid_user');
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'contributor_user_id');
    }

    public function contribution()
    {
        return $this->morphOne(Contribution::class, 'contributable');
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('contribution_status')
              ->orWhere('contribution_status', 'approved');
        });
    }

    public function scopeWirid($query)
    {
        return $query->where('kategori', 'wirid');
    }

    public function scopeDoa($query)
    {
        return $query->where('kategori', 'doa');
    }
}
