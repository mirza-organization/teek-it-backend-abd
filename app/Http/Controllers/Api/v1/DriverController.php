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

    public function submitWithdrawal(Request $request)
    {
        if (!auth()->user()->has('driver')){
            abort(404);
        }
        $user = User::find(\auth()->id());
        if (empty($user->bank_details)) {
            return response()->json(['message'=>'Please update your bank account info.'],403);
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
        return response()->json(['message'=>'Withdrawal request is successfully submitted.'],200);
    }

    public function getWithdrawalBalance()
    {
        if (!auth()->user()->has('driver')){
            abort(404);
        }
        return response()->json(['balance'=>auth()->user()->pending_withdraw*0.9],200);
    }

    public function submitBankAccountDetails(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'branch_code' => 'required',
            'bank_name' => 'required',
            'account_number' => 'required',
        ]);
        if ($validator->fails()) {
            $responseArr['message'] = $validator->errors();;
            return response()->json($responseArr, 422);
        }
        $data = ['branch' => $request->branch_code, 'bank_name' => $request->bank_name, 'account_number' => $request->account_number];
        $bankDetails = [1 => $data];
        auth()->user()->update(['bank_details' => json_encode($bankDetails)]);
        return response()->json(['message' => 'Bank Account details are successfully updated.'], 200);
    }

    public function driverAllWithdrawalRequests()
    {
        if (!auth()->user()->has('driver')){
            abort(404);
        }
        $user_id = Auth::id();
        $withdrawals = WithdrawalRequests::where('user_id', '=', $user_id)
            ->orderByDesc('created_at')->get();
        $data = array();
        foreach ($withdrawals as $key => $withdrawal) {
            $data[$key]['id']=$withdrawal->id;
            $data[$key]['user_id']=$withdrawal->user_id;
            $data[$key]['amount']=$withdrawal->amount;
            $data[$key]['status']=$withdrawal->status;
            $data[$key]['bank_detail']=json_decode($withdrawal->bank_detail);
            $data[$key]['created_at']=$withdrawal->created_at;
            $data[$key]['updated_at']=$withdrawal->updated_at;
            $data[$key]['transaction_id']=$withdrawal->transaction_id;

        }
        return response()->json(['data' => $data], 200);
    }
}
