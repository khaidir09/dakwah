<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryEpisode extends Model
{
    use HasFactory;

    protected $fillable = [
        'library_id',
        'title',
        'file_path',
        'duration',
        'sort_order',
    ];

    public function library()
    {
        return $this->belongsTo(Library::class);
    }
}
