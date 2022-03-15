<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Orders;
use App\User;
use App\VerificationCodes;
use App\WithdrawalRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class DriverController extends Controller
{

    /**
     * @param $id
     * @return mixed
     */
    public function info($id)
    {
        return User::where('id', $id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'delivery_boy');
            })->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name . ' ' . $user->l_name,
                    'lat_lng' => $user->business_location,
                    'phone' => $user->phone
                ];
            });
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function addLatLng(Request $request)
    {
        $data = [
            'business_location' => $request->latlng,
            'lat' => json_decode($request->latlng)->lat,
            'lon' => json_decode($request->latlng)->long
        ];
        return User::where('id', auth()->id())
            ->update($data);
    }

    public function submitWithdrawal(Request $request)
    {
        if (!auth()->user()->has('driver')) {
            abort(404);
        }
        $user = User::find(\auth()->id());
        if (empty($user->bank_details)) {
            return response()->json(['message' => 'Please update your bank account info.'], 403);
        }
        if ($request->has('amount')) {
            if ($user->pending_withdraw < $request->amount) {
                return response()->json(['message' => 'Requested value exceeds your current balance.'], 403);
            }
        }
        $withdrawal = $request->has('amount') ? $request->amount : $user->pending_withdraw;
        $user->pending_withdraw = $user->pending_withdraw - $withdrawal;
        $user->total_withdraw = $user->total_withdraw + $withdrawal;
        $with = new WithdrawalRequests();
        $with->user_id = \auth()->id();
        $with->amount = $withdrawal;
        $with->status = 'Pending';
        $with->bank_detail = $user->bank_details;
        $with->save();
        $user->save();
        return response()->json(['message' => 'Withdrawal request is successfully submitted.'], 200);
    }

    public function getWithdrawalBalance()
    {
        if (!auth()->user()->has('driver')) {
            abort(404);
        }
        $amount = number_format((float)\auth()->user()->pending_withdraw, 2, '.', '');
        return response()->json(['balance' => $amount], 200);
    }

    public function submitBankAccountDetails(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'branch_code' => 'required',
            'bank_name' => 'required',
            'account_number' => 'required',
            'phone' => 'required'
        ]);
        if ($validator->fails()) {
            $responseArr['message'] = $validator->errors();
            return response()->json($responseArr, 422);
        }
        $data = ['branch' => $request->branch_code, 'bank_name' => $request->bank_name, 'account_number' => $request->account_number, 'phone' => $request->phone];
        $bankDetails = [1 => $data];
        auth()->user()->update(['bank_details' => json_encode($bankDetails)]);
        return response()->json(['message' => 'Bank Account details are successfully updated.'], 200);
    }

    public function driverAllWithdrawalRequests()
    {
        if (!auth()->user()->has('driver')) {
            abort(404);
        }
        $user_id = Auth::id();
        $withdrawals = WithdrawalRequests::where('user_id', '=', $user_id)
            ->orderByDesc('created_at')->get();
        $data = array();
        foreach ($withdrawals as $key => $withdrawal) {
            $data[$key]['id'] = $withdrawal->id;
            $data[$key]['user_id'] = $withdrawal->user_id;
            $data[$key]['amount'] = $withdrawal->amount;
            $data[$key]['status'] = $withdrawal->status;
            $data[$key]['bank_detail'] = json_decode($withdrawal->bank_detail);
            $data[$key]['created_at'] = $withdrawal->created_at;
            $data[$key]['updated_at'] = $withdrawal->updated_at;
            $data[$key]['transaction_id'] = $withdrawal->transaction_id;
        }
        return response()->json(['data' => $data], 200);
    }
    /**
     * It will confirm that either the entered verification code
     * By the driver is correct or not
     * If it is correct then the 'order_status' & 'delivery_status'
     * Will be marked as 'complete'
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function checkVerificationCode(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'order_id' => 'required',
            'verification_code' => 'required|min:6|max:6'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => false,
                'message' => config('constants.MISSING_OR_INVALID_DATA')
            ], 422);
        } else {
            $verification_codes = VerificationCodes::query()->select('code->code as verification_code')
                ->where('order_id', '=', $request->order_id)
                ->get();
            $saved_code = $verification_codes[0]->verification_code;
            $given_code = $request->verification_code;
            $driver_failed_to_enter_code = ($saved_code != $given_code) ? "Yes" : "No";
            // Update table
            $verification_codes = DB::table('verification_codes')
                ->where('order_id', $request->order_id)
                ->update(['code->driver_failed_to_enter_code' => $driver_failed_to_enter_code]);
            // If the driver is failed to enter the right verification code
            if ($driver_failed_to_enter_code == "Yes") {
                $message =  config('constants.VERIFICATION_FAILED');
            } else {
                $message =  config('constants.VERIFICATION_SUCCESS');
                Orders::where('id', '=', $request->order_id)->update(['order_status' => 'complete', 'delivery_status' => 'complete']);
            }
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => $message
            ], 200);
        }
    }
}
