<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cafe extends Model
{
    //
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'menu',
        'price',
        'wifi_speed',
        'mosque',
        'maps',
        'address',
        'image_url',
        'open_time',
        'close_time'
    ];
}
