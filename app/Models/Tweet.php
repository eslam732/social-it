<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{

    protected $fillable = [
        'user_id',
        'content',
        'image',
       
        
    ];
    use HasFactory;



    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function likes()
    {
        return $this->hasMany(Likes::class);
    }

    public function retweets()
    {
        return $this->hasMany(Retweets::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    
}
