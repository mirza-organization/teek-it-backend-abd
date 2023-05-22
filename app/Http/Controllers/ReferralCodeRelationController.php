<?php

namespace App\Http\Controllers;

use App\Models\ReferralCodeRelation;
use App\Orders;
use App\Services\JsonResponseCustom;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ReferralCodeRelationController extends Controller
{
    /**
     * @version 1.0.0
     */
    public function validateReferral(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'user_id' => 'required|int',
                'referral_code' => 'required|uuid'
            ]);
            if ($validate->fails()) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    $validate->errors(),
                    config('constants.HTTP_UNPROCESSABLE_REQUEST')
                );
            }
            $is_verified = User::verifyReferralCode($request->referral_code);
            if (!$is_verified) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.INVALID_REFERRAL'),
                    config('constants.HTTP_OK')
                );
            }

            $using_referral_first_time = ReferralCodeRelation::usingReferalFirstTime($is_verified->id, $request->user_id);
            if (!$using_referral_first_time) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.REFERRAL_CAN_BE_USED_ONCE'),
                    config('constants.HTTP_OK')
                );
            }

            if (Orders::checkTotalOrders($request->user_id) === 0) {
                ReferralCodeRelation::insertReferralRelation($is_verified->id, $request->user_id);
                User::changeReferralStatus(1, $is_verified->id);
                return JsonResponseCustom::getApiResponse(
                    ['discount' => 10],
                    config('constants.TRUE_STATUS'),
                    config('constants.VALID_REFERRAL'),
                    config('constants.HTTP_OK')
                );
            } else {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.REFERRALS_ARE_ONLY_FOR_FIRST_ORDER'),
                    config('constants.HTTP_OK')
                );
            }
        } catch (Throwable $error) {
            report($error);
            return JsonResponseCustom::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
}
