<?php

namespace App\Http\Controllers;

use App\PromoCodes;
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
                'discount_percentage' => 'required|int'
            ]);
            if ($validatedData->fails()) {
                flash('Error in saving the promo code because a required field is missing or invalid data.')->error();
                return Redirect::back()->withInput($request->input());
            }
            PromoCodes::create([
                "promo_code" => $request->promo_code,
                "discount_percentage" => $request->discount_percentage,
                "order_number" => $request->order_number,
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
                'promo_code' => 'required|string|max:20'
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validatedData->errors()
                ], 422);
            }
            $count = PromoCodes::query()->where('promo_code', '=', $request->promo_code)->count();
            if ($count == 1) {
            $promo_codes = PromoCodes::query()->where('promo_code', '=', $request->promo_code)->get();
                return response()->json([
                    'data' => $promo_codes,
                    'status' => true,
                    'message' => config('constants.VALID_PROMOCODE')
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => true,
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
