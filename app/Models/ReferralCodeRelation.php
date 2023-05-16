<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCodeRelation extends Model
{
    use HasFactory;
    protected $fillable = [
        'referred_by',
        'user_id'
    ];
}
