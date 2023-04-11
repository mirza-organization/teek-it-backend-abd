<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Qty extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $guarded = [];
    protected $table = 'qty';

    public function products()
    {
        return $this->belongTo(Products::class);
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'users_id');
    }

    // public static function updateProductQty(int $product_id, int $user_id, int $product_quantity)
    // {
    //     if (!empty($user_id)) {
    //         Qty::where('products_id', $product_id)
    //             ->where('users_id', $user_id)
    //             ->update(['qty' => $product_quantity]);
    //     } else if (empty($user_id)) {
    //         Qty::where('products_id', $product_id)
    //             ->decrement(['qty' => $product_quantity]);
    //     }
    //     return true;
    // }

    public static function subtractProductQty(int $user_id, int $product_id, int $product_quantity)
    {
        return Qty::where('users_id', $user_id)
            ->where('products_id', $product_id)
            ->decrement('qty', $product_quantity);
    }

    public function getQtybyStoreAndProductId($store_id, $product_id)
    {
    }

    public static function getChildSellerProductIds(int $user_id)
    {
        return Qty::where('users_id', $user_id)->paginate(20);
    }
}
