<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preferences extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'preference_menu',
        'preference_price',
        'preference_wifi_speed',
        'preference_distance',
        'preference_mosque',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
