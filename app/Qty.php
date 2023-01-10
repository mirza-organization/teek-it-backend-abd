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
    protected $table = 'qty';
    public function products()
    {
        return $this->belongTo(Products::class);
    }
    public function product()
    {
        return $this->belongsTo(Products::class, 'users_id');
    }
}