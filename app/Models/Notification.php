<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function PHPSTORM_META\type;

class Notification extends Model
{
    use HasFactory;

    protected $fillable=[

        'creator_id',
        'notifiable_id',
        'type',
        'object_id',
    ];

    public function notifiable()
    {
        return $this->belongsTo(User::class);
    }

}
