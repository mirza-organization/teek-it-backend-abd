<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Orders;
use Illuminate\Http\Request;

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
}
