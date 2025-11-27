<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = [
        'classid',
        'date',
        'price',
        'sales_amount',
    ];
}
