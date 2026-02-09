<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $guarded = ['id'];

    public function session()
    {
        return $this->belongsTo(ChatSession::class);
    }
}
