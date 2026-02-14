<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleBibliography extends Model
{
    use HasFactory;

    protected $table = 'article_bibliography';

    protected $fillable = [
        'article_id',
        'full_citation',
        'kitab_id',
    ];

    public function scientificArticle()
    {
        return $this->belongsTo(ScientificArticle::class, 'article_id');
    }

    public function library()
    {
        return $this->belongsTo(Library::class, 'kitab_id');
    }
}
