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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }

    public function store()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
