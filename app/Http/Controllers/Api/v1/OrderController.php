<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class OrderController extends Controller
{

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function storeEstimatedTime($id, Request $request)
    {
        $order = Orders::findOrFail($id);
        $order->estimated_time = request()->estimated_time;
        $order->save();
        return $order->toArray();
    }
    /**
     * It will get order details via
     * given id
     * @version 1.0.0
     */
    public function getOrderDetails(Request $request)
    {
        try {
            $validate = Validator::make($request->route()->parameters(), [
                'id' => 'required|integer'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validate->errors()
                ], 422);
            }
            if (!Orders::where('id', $request->id)->exists()) {
                return response()->json([
                    'date' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
                ], 200);
            }
            $order = Orders::with(['user', 'delivery_boy', 'store', 'order_items', 'order_items.products'])
                ->where('id', $request->id)->first();
            return response()->json([
                'data' => $order,
                'status' => true,
                'message' => ""
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
}