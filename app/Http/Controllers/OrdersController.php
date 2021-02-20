<?php

namespace App\Http\Controllers;

use App\OrderItems;
use App\Orders;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $orders = Orders::query()->select('id')->where('user_id', '=', Auth::id());
        if (!empty($request->order_status)) {
            $orders = $orders->where('order_status', '=', $request->order_status);
        }
        $orders = $orders->paginate(100);
        $pagination = $orders->toArray();
        if (!empty($orders)) {
            $order_data = [];
            foreach ($orders as $order) {
                $order_data[] = $this->get_single_order($order->id);
            }
            unset($pagination['data']);
            $products_data = [
                'data' => $order_data,
                'status' => true,
                'message' => '',
                'pagination' => $pagination,

            ];
        } else {
            $products_data = [
                'data' => NULL,
                'status' => false,
                'message' => 'No Record Found'

            ];
        }

        return response()->json($products_data);
    }

    public function seller_orders(Request $request)
    {
        $lat = \auth()->user()->lat;
        $lng = \auth()->user()->lon;
        $users = DB::table("users")
            ->select("users.id", "users.name"
                , DB::raw("3959 * acos(cos(radians(" . $lat . "))
        * cos(radians(users.lat))
        * cos(radians(users.lon) - radians(" . $lng . "))
        + sin(radians(" . $lat . "))
        * sin(radians(users.lat))) AS distance"))
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.id', 2)
            ->whereNotNull('lat')
            ->having('distance','<',6)
            ->having('distance','>',0.0)
            ->orderBy('distance')
            ->get()
            ->pluck('id')
            ->toArray();
        $orders = Orders::query();
        if (!empty($request->order_status)) {
            $orders = $orders->where('order_status', '=', $request->order_status);
            $orders = $orders
                ->whereHas('order_items.products', function ($q) use ($users) {
                    $q->whereHas('user', function ($w) use ($users) {
                        $w->whereIn('id', $users);
                    });
                });
            if (\auth()->user()->vehicle_type == 'bike') {
                $orders = $orders->whereHas('order_items.products', function ($q) {
                    return $q->where('bike', 1);
                });
            }
        }
        $orders = $orders->paginate();
        $pagination = $orders->toArray();
        if (!empty($orders)) {
            $order_data = [];
            foreach ($orders as $order) {
                $order_data[] = $this->get_single_order($order->id);
            }
            unset($pagination['data']);
            $products_data = [
                'data' => $order_data,
                'status' => true,
                'message' => '',
                'pagination' => $pagination,

            ];
        } else {
            $products_data = [
                'data' => NULL,
                'status' => false,
                'message' => 'No Record Found'

            ];
        }
        return response()->json($products_data);
    }

    public function delivery_boy_orders(Request $request, $delivery_boy_id)
    {
        $orders = Orders::query()->select('id')->where('delivery_boy_id', '=', $delivery_boy_id)->where('delivery_status', '=', $request->delivery_status)->paginate();
        $pagination = $orders->toArray();
        if (!empty($orders)) {
            $order_data = [];
            foreach ($orders as $order) {
                $order_data[] = $this->get_single_order($order->id);
            }
            unset($pagination['data']);
            $products_data = [
                'data' => $order_data,
                'status' => true,
                'message' => '',
                'pagination' => $pagination,

            ];
        } else {
            $products_data = [
                'data' => NULL,
                'status' => false,
                'message' => 'No Record Found'

            ];
        }
        return response()->json($products_data);
    }

    public function assign_order(Request $request)
    {
        $products_data = [
            'data' => NULL,
            'status' => false,
            'message' => 'No Record Found'

        ];
        $order = Orders::find($request->order_id);
        if (!empty($order)) {
            $order->delivery_status = $request->delivery_status;
            $order->delivery_boy_id = $request->delivery_boy_id;
            $order->order_status = $request->order_status;
            $order->save();
            $products_data = [
                'data' => NULL,
                'status' => true,
                'message' => 'Assigned'

            ];
        }
        return response()->json($products_data);
    }


    public function update_assign(Request $request)
    {
        $products_data = [
            'data' => NULL,
            'status' => false,
            'message' => 'No Record Found'

        ];
        $order = Orders::find($request->order_id);
        if (!empty($order)) {
            $order->delivery_status = $request->delivery_status;
            $order->delivery_boy_id = $request->delivery_boy_id;
            $order->order_status = $request->order_status;
            if ($request->payment_status == "paid" && $order->payment_status != "paid" && $request->order_status == 'complete' && $order->order_status != 'complete' && $request->delivery_status == 'delivered' && $order->delivery_status != 'delivered') {
                $user = User::find($order->seller_id);
                $user_money = $user->pending_withdraw;
                $user->pending_withdraw = $order->order_total + $user_money;
                $user->save();
                $this->calculateDriverFair($order,$user);
            }

            $order->driver_charges = $request->driver_charges;
            $order->driver_traveled_km = $request->driver_traveled_km;
            $order->save();
            $products_data = [
                'data' => NULL,
                'status' => true,
                'message' => 'Updated'

            ];
        }

        return response()->json($products_data);
    }

    public function new(Request $request)
    {
        $grouped_seller = [];
        foreach ($request->items as $item) {
            $product_id = $item['product_id'];
            $qty = $item['qty'];
            $product_price = (new ProductsController())->get_product_price($product_id);
            $product_seller_id = (new ProductsController())->get_product_seller_id($product_id);
            $temp = [];
            $temp['qty'] = $qty;
            $temp['product_id'] = $product_id;
            $temp['price'] = $product_price;
            $temp['seller_id'] = $product_seller_id;
            $grouped_seller[$product_seller_id][] = $temp;
        }


        $order_arr = [];
        foreach ($grouped_seller as $seller_id => $order) {
            $total = 0;
            $user_id = Auth()->id();
            $order_total = 0;
            $total_items = 0;
            foreach ($order as $order_item) {
                $total_items = $total_items + $order_item['qty'];
                $order_total = $order_total + ($order_item['price'] * $order_item['qty']);
            }

            $user = User::find($seller_id);
            $user_money = $user->pending_withdraw;
            $user->pending_withdraw = $order_total + $user_money;
            $user->save();

            $new_order = new Orders();
            $new_order->user_id = $user_id;
            $new_order->order_total = $order_total;
            $new_order->total_items = $total_items;
            $new_order->lat = "NULL";
            $new_order->lng = "NULL";
            $new_order->phone_number = "NULL";
            $new_order->address = "NULL";
            $new_order->payment_status = $request->payment_status ?? "hidden";
            $new_order->seller_id = $seller_id;
            $new_order->save();
            $order_id = $new_order->id;
            $order_arr[] = $order_id;
            foreach ($order as $order_item) {
                $new_order_item = new OrderItems();
                $new_order_item->order_id = $order_id;
                $new_order_item->product_id = $order_item['product_id'];
                $new_order_item->product_price = $order_item['price'];
                $new_order_item->product_qty = $order_item['qty'];
                $new_order_item->save();
            }
        }
        $return_data = $this->get_orders_from_ids($order_arr);
        $user_arr = [
            'data' => $return_data,
            'status' => true,
            'message' => 'Order Added Successfully'

        ];
        return response()->json($user_arr, 200);
    }


    public function updateOrder(Request $request)
    {
        $order_ids = $request->ids;
        $order_arr = explode(',', $order_ids);
        foreach ($order_arr as $order_id) {

            $order = Orders::find($order_id);
            if ($request->payment_status == "paid" && $order->payment_status != "paid" && $request->order_status == 'complete' && $order->order_status != 'complete' && $request->delivery_status == 'delivered' && $order->delivery_status != 'delivered') {
                $user = User::find($order->seller_id);
                $user_money = $user->pending_withdraw;
                $user->pending_withdraw = $order->order_total + $user_money;
                $user->save();
//                $this->calculateDriverFair($order, $user);
            }
            $order->lat = $request->lat;
            $order->lng = $request->lng;
            $order->phone_number = $request->phone_number;
            $order->address = $request->address;
            $order->payment_status = $request->payment_status;
            $order->order_status = $request->order_status;
            $order->transaction_id = $request->transaction_id;

            $order->driver_charges = $request->driver_charges;
            $order->driver_traveled_km = $request->driver_traveled_km;
            $order->save();

        }
        $return_data = $this->get_orders_from_ids($order_arr);
        $user_arr = [
            'data' => $return_data,
            'status' => true,
            'message' => 'Order Added Successfully'
        ];
        return response()->json($user_arr, 200);
    }


    public function get_orders_from_ids($ids)
    {
        $raw_data = [];
        foreach ($ids as $order_id) {
            $raw_data[] = $this->get_single_order($order_id);
        }
        return $raw_data;
    }

    public function get_single_order($order_id)
    {
        $temp = [];
        $order = Orders::find($order_id);
        $temp['order'] = $order;
        $temp['order_items'] = OrderItems::query()->where('order_id', '=', $order_id)->get();
        $temp['seller'] = User::find($order->seller_id);
        return $temp;
    }

    public function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'miles') {
        $theta = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        switch($unit) {
            case 'miles':
                break;
            case 'kilometers' :
                $distance = $distance * 1.609344;
        }
        return (round($distance,2));
    }

    private function calculateDriverFair($order, $user)
    {
        $childOrders = Orders::where('delivery_boy_id', $order->delivery_boy_id)->where('order_status', 'onTheWay')->get();
        if (count($childOrders) > 0) {
            foreach ($childOrders as $childOrder) {
                $childOrder->update(['parent_id' => $order->id]);
            }
        }
        $driver = User::find($order->delivery_boy_id);
        $driver_money = $driver->pending_withdraw;
        if (is_null($order->parent_id)) {
            $distance = $this->getDistanceBetweenPointsNew($order->lat, $order->lng, $user->lat, $user->lon);
            $totalFair = ($distance * 1.50) + 1.10 + 1.50;
            $driver->pending_withdraw = ($totalFair - 3.50) + $driver_money;
            $driver->save();
            $order->driver_charges = $totalFair - 3.50;
            $order->driver_traveled_km = (round(($distance * 1.609344), 2));
            $order->save();
        } else {
            $oldOrder = Orders::find($order->parent_id);
            $distance = $this->getDistanceBetweenPointsNew($order->lat, $order->lng, $oldOrder->lat, $oldOrder->lon);
            $pickup = $oldOrder->seller_id == $order->seller_id ? 0.0 : 1.50;
            $totalFair = ($distance * 1.50) + 1.10 + $pickup;
            $driver->pending_withdraw = ($totalFair - 3.50) + $driver_money;
            $driver->save();
        }
    }

}
