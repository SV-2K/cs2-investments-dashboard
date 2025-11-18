<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $primaryKey = 'classid';
    protected $fillable = [
        'type_id',
        'name',
        'market_name',
        'name_color',
        'icon_url',
    ];
}
