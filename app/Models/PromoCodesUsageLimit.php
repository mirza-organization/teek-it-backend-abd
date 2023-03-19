<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCodesUsageLimit extends Model
{
    use HasFactory;
    protected $table = 'promo_codes_usage_limit';
    /*
    |--------------------------------------------------------------------------
    | Custom Helper Functions
    |--------------------------------------------------------------------------
    */

    /**
     * function will return boolean values i.e 1 = true, 0 = false
     */
    public static function promoCodeUsageLimit(object $promo_code_data, int $user_id)
    {
        $usage_limit_data = PromoCodesUsageLimit::where('promo_code_id', $promo_code_data->id)
            ->where('user_id', $user_id)
            ->first();
        $status = 0;
        if (empty($usage_limit_data)) {
            $usage_limit_data = new PromoCodesUsageLimit;
            $usage_limit_data->promo_code_id = $promo_code_data->id;
            $usage_limit_data->user_id = $user_id;
            $usage_limit_data->total_used = 1;
            $usage_limit_data->save();
            $status = 1;
        } elseif ($usage_limit_data->total_used < $promo_code_data->usage_limit) {
            $usage_limit_data->increment('total_used');
            $status = 1;
        }
        return $status;
    }

    public static function promoCodeTotalUsedByUser(int $user_id, int $promo_code_id)
    {
        return PromoCodesUsageLimit::where('promo_code_id', $promo_code_id)
            ->where('user_id', $user_id)
            ->first();
    }
}
