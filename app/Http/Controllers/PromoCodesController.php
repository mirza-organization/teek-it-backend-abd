<?php

namespace App\Http\Controllers;

use App\Orders;
use App\PromoCodes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PromoCodesController extends Controller
{
    /**
     * Returns promo codes form & list view
     * @version 1.0.0
     */
    public function promocodesHome()
    {
        if (Auth::user()->hasRole('superadmin')) {
            $promo_codes = PromoCodes::paginate(10);
            return view('admin.promo_codes', compact('promo_codes'));
        } else {
            abort(404);
        }
    }
    /**
     * Adds promo code into the database
     * @version 1.0.0
     */
    public function promocodesAdd(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'promo_code' => 'required|string|unique:promo_codes|max:20',
                'discount_percentage' => 'required|int',
                'expiry_dt' => 'required'
            ]);
            if ($validatedData->fails()) {
                flash('Error in saving the promo code because a required field is missing or invalid data.')->error();
                return Redirect::back()->withInput($request->input());
            }
            PromoCodes::create([
                "promo_code" => $request->promo_code,
                "discount_percentage" => $request->discount_percentage,
                "order_number" => $request->order_number,
                "expiry_dt" => $request->expiry_dt,
            ]);
            flash('Promo code saved successfully.')->success();
            return back();
        } catch (Throwable $error) {
            report($error);
            flash('Failed to save promo code due to some internal error.')->error();
            return back();
        }
    }
    /**
     * Validates either the given promo code is correct or not
     * It also checks that either the user is submitting this
     * Promo code for the right order number or not 
     * @version 1.0.0
     */
    public function promocodesValidate(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'user_id' => 'required|int',
                'promo_code' => 'required|string|max:20'
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validatedData->errors()
                ], 422);
            }
            $promocodes_count = PromoCodes::query()->where('promo_code', '=', $request->promo_code)->count();
            if ($promocodes_count == 1) {
                $expiry_dt = PromoCodes::where('promo_code', '=', $request->promo_code)->pluck('expiry_dt')->first();
                $current_date = date('Y-m-d');
                if ($expiry_dt < $current_date) {
                    return response()->json([
                        'data' => [],
                        'status' => false,
                        'message' =>  config('constants.EXPIRED_PROMOCODE')
                    ], 200);
                } else {
                    $promo_codes = PromoCodes::query()->where('promo_code', '=', $request->promo_code)->get();
                    if (!empty($promo_codes[0]->order_number)) {
                        $orders_count = Orders::query()->where('user_id', '=', $request->user_id)->count();
                        if ($promo_codes[0]->order_number == $orders_count) {
                            return response()->json([
                                'data' => $promo_codes,
                                'status' => true,
                                'message' => config('constants.VALID_PROMOCODE')
                            ], 200);
                        } else {
                            return response()->json([
                                'data' => [],
                                'status' => false,
                                'message' => 'This promo code is only valid for order#' . $promo_codes[0]->order_number
                            ], 200);
                        }
                    }
                    return response()->json([
                        'data' => $promo_codes,
                        'status' => true,
                        'message' => config('constants.VALID_PROMOCODE')
                    ], 200);
                }
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.INVALID_PROMOCODE')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * Deletes the specific promo code via ajax call
     */
    public function promoCodesDel(Request $request)
    {
        try {
            if (Auth::user()->hasRole('superadmin')) {
                for ($i = 0; $i < count($request->promocodes); $i++) {
                    PromoCodes::where('id', '=', $request->promocodes[$i])->delete();
                }
                return response("Promocodes Deleted Successfully");
            }
        } catch (Throwable $error) {
            report($error);
            flash('Failed to delete promo code due to some internal error.')->error();
            return back();
        }
    }
    /**
     * Updates the specific promo code via popup modal
     */
    public function promoCodesUpdate(Request $request, $id)
    {
        $validatedData = Validator::make($request->all(), [
            'promo_code' => 'required|string|max:20',
            'discount_percentage' => 'required|int',
            'expiry_dt' => 'required'
        ]);
        if ($validatedData->fails()) {
            flash('Error in saving the promo code because a required field is missing or invalid data.')->error();
            return Redirect::back()->withInput($request->input());
        }
        $promo_code = PromoCodes::find($id);
        $promo_code->promo_code = $request->promo_code;
        $promo_code->discount_percentage = $request->discount_percentage;
        $promo_code->order_number = $request->order_number;
        $promo_code->expiry_dt = $request->expiry_dt;
        $promo_code->save();
        flash('Promo code updated successfully.')->success();
        return back();
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\PromoCodes  $promoCodes
     * @return \Illuminate\Http\Response
     */
    public function show(PromoCodes $promoCodes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PromoCodes  $promoCodes
     * @return \Illuminate\Http\Response
     */
    public function edit(PromoCodes $promoCodes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PromoCodes  $promoCodes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PromoCodes $promoCodes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PromoCodes  $promoCodes
     * @return \Illuminate\Http\Response
     */
    public function destroy(PromoCodes $promoCodes)
    {
        //
    }
}