<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $guarded = [];

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
