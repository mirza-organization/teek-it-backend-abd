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
            return view('admin.promo_codes');
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
                'promo_code' => 'required|string|unique:promo_codes|max:10',
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
