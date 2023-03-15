<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatUsers extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chat_id',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
