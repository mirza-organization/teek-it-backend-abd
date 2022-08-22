<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthController;
use App\productImages;
use App\Products;
use App\Rattings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RattingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    //    public function index()
    //    {
    //        //
    //    }
    //
    //    /**
    //     * Show the form for creating a new resource.
    //     *
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function create()
    //    {
    //        //
    //    }
    //
    //    /**
    //     * Store a newly created resource in storage.
    //     *
    //     * @param  \Illuminate\Http\Request  $request
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function store(Request $request)
    //    {
    //        //
    //    }
    //
    //    /**
    //     * Display the specified resource.
    //     *
    //     * @param  \App\Rattings  $rattings
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function show(Rattings $rattings)
    //    {
    //        //
    //    }
    //
    //    /**
    //     * Show the form for editing the specified resource.
    //     *
    //     * @param  \App\Rattings  $rattings
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function edit(Rattings $rattings)
    //    {
    //        //
    //    }
    //
    //    /**
    //     * Update the specified resource in storage.
    //     *
    //     * @param  \Illuminate\Http\Request  $request
    //     * @param  \App\Rattings  $rattings
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function update(Request $request, Rattings $rattings)
    //    {
    //        //
    //    }
    //
    //    /**
    //     * Remove the specified resource from storage.
    //     *
    //     * @param  \App\Rattings  $rattings
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function destroy(Rattings $rattings)
    //    {
    //        //
    //    }



    public function add(Request $request)
    {
        $validate = Rattings::validator($request);
        if ($validate->fails()) {
            $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
            return response()->json($response, 400);
        }
        $user_id = Auth::id();
        $response = [];
        $ratting = new Rattings();
        $ratting->user_id = $user_id;
        $ratting->product_id = $request->get('product_id');
        $ratting->ratting = $request->get('ratting');
        //  $ratting->save();
        return (new ProductsController)->view($request->get('product_id'));
    }

    public function update(Request $request)
    {
        $validate = Rattings::updateValidator($request);
        if ($validate->fails()) {
            $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
            return response()->json($response, 400);
        }
        $user_id = Auth::id();
        $response = [];
        $ratting = Rattings::find($request->get('id'));
        //  $ratting->user_id=$user_id;
        //  $ratting->product_id=$request->get('product_id');
        $ratting->ratting = $request->get('ratting');
        $ratting->save();
        return (new ProductsController)->view($request->get('product_id'));
    }

    public function delete($ratting_id)
    {
        try {
            $delete_rating =  Rattings::find($ratting_id);
            if ($delete_rating) {
                $delete_rating->delete();
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.ITEM_DELETED'),
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
                ], 200);
            }
            // return (new ProductsController)->all();
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }








    public function get_ratting($product_id)
    {
        $raw_ratting = Rattings::query()->where('product_id', '=', $product_id);
        $average = $raw_ratting->avg('ratting');
        $all_raw = $raw_ratting->get();
        $all = [];
        foreach ($all_raw as $aw) {
            $aw->user = (new AuthController)->get_user($aw->user_id);
            $all[] = $aw;
        }
        return ['average' => $average, 'all' => $all];
    }
}