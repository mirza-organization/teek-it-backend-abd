<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromoCodes extends Model
{
    protected $fillable = [
        'promo_code',
        'discount_type',
        'discount',
        'order_number',
        'usage_limit',
        'min_discount',
        'max_discount',
        'store_id',
        'expiry_dt'
    ];
}