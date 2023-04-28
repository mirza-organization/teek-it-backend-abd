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
    /**
     * Relations
     */
    public function products()
    {
        return $this->belongTo(Products::class);
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'users_id');
    }
    /**
     * Helpers
     */
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

    public static function getChildSellerProducts(int $user_id)
    {
        return Qty::where('qty.users_id', $user_id)
            ->join('products as prod', 'prod.id', 'qty.products_id')
            ->select('prod.*')
            ->paginate(20);
    }

    public static function updateChildProductQty(int $user_id, int $product_id, int $qty)
    {
        return Qty::where('users_id', $user_id)
            ->where('products_id', $product_id)
            ->update([
                'qty' => $qty
            ]);
    }
}
