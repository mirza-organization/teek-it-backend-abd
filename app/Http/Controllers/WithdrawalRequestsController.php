<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthController;
use App\User;
use App\WithdrawalRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalRequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\WithdrawalRequests  $withdrawalRequests
     * @return \Illuminate\Http\Response
     */
    public function show(WithdrawalRequests $withdrawalRequests)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\WithdrawalRequests  $withdrawalRequests
     * @return \Illuminate\Http\Response
     */
    public function edit(WithdrawalRequests $withdrawalRequests)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\WithdrawalRequests  $withdrawalRequests
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WithdrawalRequests $withdrawalRequests)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\WithdrawalRequests  $withdrawalRequests
     * @return \Illuminate\Http\Response
     */
    public function destroy(WithdrawalRequests $withdrawalRequests)
    {
        //
    }


    /**
     *It will send withdrawl request    
     * @version 1.0.0
     */
    public function sendRequest(Request $request)
    {
        $user_id = Auth::id();

        $user = User::query()->find($user_id);
        $status = "Pending";
        $bank_detail = $request->bank_detail;
        $amount = $user->wallet;


        $withdrawal = new WithdrawalRequests();
        $withdrawal->user_id = $user_id;
        $withdrawal->amount = $amount;
        $withdrawal->bank_detail = $bank_detail;
        $withdrawal->status = $status;
        $withdrawal->save();

        $user->wallet = 0.0;
        $user->save();
        return $this->getRequests();
    }
    /**
     *Fetch withdrawl requests of logged in user   
     * @version 1.0.0
     */
    public function getRequests()
    {
        $user_id = Auth::id();
        $return_data = WithdrawalRequests::query()->where('user_id', '=', $user_id)->get();
        $user_arr = [
            'data' => $return_data,
            'status' => true,
            'message' => ''

        ];
        return response()->json($user_arr);
    }
}