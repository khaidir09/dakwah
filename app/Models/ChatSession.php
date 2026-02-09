<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    protected $guarded = ['id'];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
