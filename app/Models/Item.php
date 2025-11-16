<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $primaryKey = 'asset_id';
    public $incrementing = false;
    protected $fillable = [
        'classid',
        'user_id',
    ];
}
