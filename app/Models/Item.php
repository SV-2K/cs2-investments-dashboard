<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    protected $primaryKey = 'asset_id';
    public $incrementing = false;
    protected $fillable = [
        'classid',
        'user_id',
    ];

    public function type(): HasOne
    {
        return $this->hasOne(ItemType::class, 'classid', 'classid');
    }
}
