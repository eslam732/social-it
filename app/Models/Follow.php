<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

   protected $fillable=[
    'followed_user_id',
    'follower_user_id'
    ];
}
