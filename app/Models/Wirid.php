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

    public function scopeWirid($query)
    {
        return $query->where('kategori', 'wirid');
    }

    public function scopeDoa($query)
    {
        return $query->where('kategori', 'doa');
    }
}
