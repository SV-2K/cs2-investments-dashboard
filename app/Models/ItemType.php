<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    protected $fillable = [
        'classid',
        'name',
        'market_name',
        'name_color',
        'icon_url',
    ];
}
