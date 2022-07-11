<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromoCodes extends Model
{
    protected $fillable = [
        'promo_code',
        'discount_percentage',
        'order_number'
    ];
}
