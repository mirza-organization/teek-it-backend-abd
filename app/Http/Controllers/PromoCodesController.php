<?php

namespace App\Http\Controllers;

use App\Orders;
use App\PromoCodes;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            //get stores names for select dropdown
            $stores = \DB::select('SELECT *
            FROM users A
             LEFT JOIN role_user B
            ON A.id= B.user_id WHERE B.role_id=2');
            return view('admin.promo_codes', compact('promo_codes', 'stores'));
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
                'discount_type' => 'required',
                'discount' => 'required|int',
                'min_amnt_for_discount' => 'required|int',
                'max_amnt_for_discount' => 'required|int',
                'expiry_dt' => 'required',
            ]);
            if ($validatedData->fails()) {
                flash('Error in saving the promo code because a required field is missing or invalid data.')->error();
                return Redirect::back()->withInput($request->input());
            }
            PromoCodes::create([
                "promo_code" => $request->promo_code,
                "discount_type" => $request->discount_type,
                "discount" => $request->discount,
                "order_number" => $request->order_number,
                "usage_limit" => $request->usage_limit,
                "min_amnt_for_discount" => $request->min_amnt_for_discount,
                "max_amnt_for_discount" => $request->max_amnt_for_discount,
                "store_id" => $request->store_id,
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
     * Deletes the specific promo code via ajax call
     * @version 1.0.0
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
     * @version 1.0.0
     */
    public function promoCodesUpdate(Request $request, $id)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'promo_code' => 'required|string|max:20',
                'discount_type' => 'required',
                'discount' => 'required|int',
                'min_amnt_for_discount' => 'required|int',
                'max_amnt_for_discount' => 'required|int',
                'expiry_dt' => 'required',
            ]);
            if ($validatedData->fails()) {
                flash('Error in saving the promo code because a required field is missing or invalid data.')->error();
                return Redirect::back()->withInput($request->input());
            }
            $promo_code = PromoCodes::find($id);
            $promo_code->promo_code = $request->promo_code;
            $promo_code->discount_type = $request->discount_type;
            $promo_code->discount = $request->discount;
            $promo_code->order_number = $request->order_number;
            $promo_code->usage_limit = $request->usage_limit;
            $promo_code->min_amnt_for_discount = $request->min_amnt_for_discount;
            $promo_code->max_amnt_for_discount = $request->max_amnt_for_discount;
            $promo_code->expiry_dt = $request->expiry_dt;
            $promo_code->store_id = $request->store_id;
            $promo_code->save();
            flash('Promo code updated successfully.')->success();
            return back();
        } catch (Throwable $error) {
            report($error);
            flash('Failed to update promo code due to some internal error.')->error();
            return back();
        }
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
    /**
     * Validates either the given promo code is correct or not
     * It also checks that either the user is submitting this
     * Promo code for the right order number or not 
     * @version 1.2.0
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
                    if (empty($promo_codes[0]->store_id)) ($promo_codes[0]->store_id = 'NA');
                    //below query will pass required data to our helper functions down below to validate
                    $promo_code_data = PromoCodes::where('promo_code', $request->promo_code)->first(['id', 'usage_limit', 'store_id', 'discount']);
                    if (!empty($promo_codes[0]->order_number)) {
                        $user_orders_count = Orders::query()->where('user_id', '=', $request->user_id)->count();
                        if ($promo_codes[0]->order_number == $user_orders_count + 1) {
                            if (!empty($promo_code_data->usage_limit)) {
                                if ($this->promoCodeUsageLimit($promo_code_data) == 1) {
                                    return response()->json([
                                        'data' => [
                                            'promo_code' => $promo_codes[0],
                                            'store' => ($this->ifPromoCodeBelongsToStore($promo_code_data)) ? ($this->ifPromoCodeBelongsToStore($promo_code_data)) : ('NA'),
                                        ],
                                        'status' => true,
                                        'message' => config('constants.VALID_PROMOCODE')
                                    ], 200);
                                } else {
                                    return response()->json([
                                        'data' => [],
                                        'status' => false,
                                        'message' => config('constants.MAX_LIMIT')
                                    ], 200);
                                }
                            }
                        } else {
                            return response()->json([
                                'data' => [],
                                'status' => false,
                                'message' => 'This promo code is only valid for order#' . $promo_codes[0]->order_number
                            ], 200);
                        }
                    }
                    $this->promoCodeUsageLimit($promo_code_data);
                    return response()->json([
                        'data' => [
                            'promo_code' => $promo_codes[0],
                            'store' => ($this->ifPromoCodeBelongsToStore($promo_code_data)) ? ($this->ifPromoCodeBelongsToStore($promo_code_data)) : ('NA'),
                        ],
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
     * function will return boolean values i.e 1 = true, 0 = false
     */
    public function promoCodeUsageLimit($promo_code_data)
    {
        $status = 1;
        $usage_limit = DB::table('promo_codes_usage_limit')->where('user_id', '=', Auth::id())->first();
        if (empty($usage_limit)) {
            DB::table('promo_codes_usage_limit')->insert([
                'user_id' => Auth::id(),
                'promo_code_id' =>  $promo_code_data->id,
                'total_used' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $status = 1;
        } else {
            if ($usage_limit->total_used < $promo_code_data->usage_limit) {
                DB::table('promo_codes_usage_limit')
                    ->where('promo_code_id', '=', $promo_code_data->id)
                    ->where('user_id', '=', Auth::id())
                    ->increment('total_used', 1);
                $status = 1;
            } else {
                $status = 0;
            }
        }
        return $status;
    }
    /**
     * function will check if promo code belongs to a specific store
     */
    public function ifPromoCodeBelongsToStore($promo_code_data)
    {
        $store = User::where('id', $promo_code_data->store_id)->first();
        if (empty($store)) {
        } else {
            $data = [
                'id' => $store->id,
                'name' => $store->business_name,
                'discount' => $promo_code_data->discount,
            ];
            return $data;
        }
    }
}