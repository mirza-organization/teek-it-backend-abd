<?php

namespace App\Http\Controllers;

use App\Models\ReferralCodeRelation;
use App\Orders;
use App\Services\JsonResponseCustom;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReferralCodeRelationController extends Controller
{
    /**
     * 1) Validate the given referral from the users model.
     * 2) Check that the friend who is using this referral have not placed any order yet.
     * 3) Check from the ReferralCodeRelation model that either this friend is using this referral for the 1st time.
     */

    /**
     * @version 1.0.0
     */
    public function validateReferral(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'user_id' => 'required|int',
            'referral_code' => 'required|uuid'
        ]);
        if ($validate->fails()) {
            return JsonResponseCustom::getApiResponse(
                [],
                false,
                $validate->errors(),
                config('constants.HTTP_UNPROCESSABLE_REQUEST')
            );
        }
        User::verifyReferralCode();
        ReferralCodeRelation::usingReferalFirstTime();
        Orders::checkTotalOrders(1);
    }
}
