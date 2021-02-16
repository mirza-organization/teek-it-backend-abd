<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\User;
use App\WithdrawalRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function submitWithdrawal($amount)
    {
        if (!auth()->user()->has('driver')){
            abort(404);
        }
        if (auth()->user()->pending_withdraw < $amount) {
            return response()->json(['message'=>'Your request to withdrawal amount is not correct.'],403);
        }
        $user = User::find(\auth()->id());
        if (empty($user->bank_details)) {
            return response()->json(['message'=>'Please update your bank account info.'],403);
        }
        $user->pending_withdraw = $user->pending_withdraw - $amount;
        $user->total_withdraw = $user->total_withdraw + $amount;
        $with = new WithdrawalRequests();
        $with->user_id = \auth()->id();
        $with->amount = $amount;
        $with->status = 'Pending';
        $with->bank_detail = $user->bank_details;
        $with->save();
        $user->save();
        return response()->json(['message'=>'Withdrawal request is successfully submitted.'],200);
    }

    public function getWithdrawalBalance()
    {
        if (!auth()->user()->has('driver')){
            abort(404);
        }
        return response()->json(['amount'=>auth()->user()->pending_withdraw*0.9],200);
    }

    public function submitBankAccountDetails(Request $request)
    {
        $data = ['branch' => $request->branch_code, 'bank_name' => $request->bank_name, 'account_number' => $request->account_number];
        auth()->user()->update(['bank_detailes' => json_encode($data)]);
        return response()->json(['message' => 'Bank Account details are successfully updated.'], 200);
    }

    public function driverAllWithdrawalRequests()
    {
        if (!auth()->user()->has('driver')){
            abort(404);
        }
        $user_id = Auth::id();
        $withdrawals = WithdrawalRequests::where('user_id', '=', $user_id)->get();
        $data = [
            'amount'=>$withdrawals->amount,
            'status'=>$withdrawals->status,
            'transaction_id'=>$withdrawals->transaction_id,
            'date_time' => $withdrawals->created_at
        ];
        return response()->json(['data' => json_encode($data)], 200);
    }
}
