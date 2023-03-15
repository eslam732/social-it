<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'about',
        'private',
        'picture',
        'following',
        'followers',
        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
        'verified',
        'email_verified_at'
    ];

   

    public function tweets()
    {
        return $this->hasMany(Tweet::class);
    }

    public function retweets()
    {
        return $this->hasMany(Retweets::class);
    }

    public function likes()
    {
        return $this->hasMany(Likes::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }



    public function followRequests()
    {
        return $this->hasMany(followRequests::class);
    }
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function chat_users()
    {
        return $this->hasMany(ChatUsers::class);
    }



    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
