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

    public static function usingReferalFirstTime(int $referred_by, int $user_id)
    {
        $data = ReferralCodeRelation::where('referred_by', $referred_by)->where('user_id', $user_id)->first();
        return (is_null($data)) ? true :  false;
    }

    public static function insertReferralRelation(int $referred_by, int $user_id)
    {
        return ReferralCodeRelation::create([
            'referred_by' => $referred_by,
            'user_id'   => $user_id
        ]);
    }
}
