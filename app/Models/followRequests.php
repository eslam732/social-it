<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class followRequests extends Model
{
    use HasFactory;

    protected $fillable=[
        
        'follower_user_id',
        'followed_user_id'
        ];


        public function user()
    {
        return $this->belongsTo(User::class);
    }
}


