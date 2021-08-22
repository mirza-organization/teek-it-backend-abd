<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $fillable = ['*'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function order_items()
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function order_item_products()
    {
        return $this->hasManyThrough(OrderItems::class,Products::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
