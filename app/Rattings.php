<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Rattings extends Model
{
    //
    public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'ratting' => 'required',
            'product_id' => 'required',
        ]);
    }
    public static function updateValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'ratting' => 'required',
            'product_id' => 'required',
            'id' => 'required',
        ]);
    }
}
