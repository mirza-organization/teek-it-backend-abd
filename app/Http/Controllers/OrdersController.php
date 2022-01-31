<?php

namespace App\Http\Controllers;

use App\OrderItems;
use App\Orders;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrdersController extends Controller
{
    /**
     * List orders w.r.t Seller ID
     * @author Huzaifa Haleem
     * @version 1.1.0
     */
    public function index(Request $request)
    {
        $orders = Orders::query()->select('id')->where('user_id', '=', Auth::id());

        if (!empty($request->order_status)) {
            $orders = $orders->where('order_status', '=', $request->order_status);
        }
        $orders = $orders->paginate(20);
        $pagination = $orders->toArray();
        if (!$orders->isEmpty()) {
            $order_data = [];
            foreach ($orders as $order) {
                $order_data[] = $this->get_single_order($order->id);
            }
            unset($pagination['data']);
            return response()->json([
                'data' => $order_data,
                'status' => true,
                'message' => '',
                'pagination' => $pagination
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        }
    }

    public function seller_orders(Request $request)
    {
        $lat = \auth()->user()->lat;
        $lng = \auth()->user()->lon;
        $orders = array();
        $assignedOrders = Orders::where('delivery_boy_id', \auth()->id())->where('delivery_status', 'assigned')->get();
        if ($request->has('order_status') && $request->order_status == 'delivered') {
            $orders = Orders::query();
            $orders = $orders->where('order_status', '=', $request->order_status);
            $orders = $orders->orderByDesc('created_at')->paginate();
            $pagination = $orders->toArray();
        } elseif ($request->has('order_status') && $request->order_status == 'ready') {
            if (count($assignedOrders) == 0) {
                $users = DB::table("users")
                    ->select(
                        "users.id",
                        "users.name",
                        DB::raw("3959 * acos(cos(radians(" . $lat . "))
                            * cos(radians(users.lat))
                            * cos(radians(users.lon) - radians(" . $lng . "))
                            + sin(radians(" . $lat . "))
                            * sin(radians(users.lat))) AS distance")
                    )
                    ->join('role_user', 'users.id', '=', 'role_user.user_id')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->where('roles.id', 2)
                    ->whereNotNull('lat')
                    ->having('distance', '<', 6)
                    ->having('distance', '>', 0.0)
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
                $orders = $orders->where('type', 'delivery')
                    ->orderByDesc('created_at')->paginate();
                $pagination = $orders->toArray();
            } else {
                $assignedOrders = $assignedOrders[0];
                $nearbyOrders = DB::table("orders")
                    ->select(
                        "orders.id",
                        DB::raw("3959 * acos(cos(radians(" . $assignedOrders->lat . "))
                        * cos(radians(orders.lat))
                        * cos(radians(orders.lng) - radians(" . $assignedOrders->lng . "))
                        + sin(radians(" . $assignedOrders->lat . "))
                        * sin(radians(orders.lat))) AS distance")
                    )
                    ->where(function ($q) {
                        $q->where('order_status', 'pending')
                            ->orWhere('order_status', 'ready');
                    })
                    ->whereNotNull('lat')
                    ->having('distance', '<', 2)
                    ->having('distance', '>', 0.0)
                    ->orderBy('distance')
                    ->get()
                    ->pluck('id')
                    ->toArray();
                $orders = Orders::query();
                $orders = $orders->where('order_status', '=', $request->order_status)
                    ->where('delivery_boy_id', \auth()->id());
                $orders = $orders->orWhere(function ($q) use ($nearbyOrders) {
                    $q->whereIn('id', $nearbyOrders);
                    if (\auth()->user()->vehicle_type == 'bike') {
                        $q->whereHas('order_items.products', function ($query) {
                            return $query->where('bike', 1);
                        });
                    }
                });
                $orders = $orders->where('type', 'delivery')
                    ->orderByDesc('created_at')->paginate();
                $pagination = $orders->toArray();
            }
        } else {
            $orders = Orders::query();
            $orders = $orders->where('delivery_boy_id', \auth()->id());
            $orders = $orders->orderByDesc('created_at')->paginate();
            $pagination = $orders->toArray();
        }
        if (!$orders->isEmpty()) {
            $order_data = [];
            foreach ($orders as $order) {
                $order_data[] = $this->get_single_order($order->id);
            }
            unset($pagination['data']);
            return response()->json([
                'data' => $order_data,
                'status' => true,
                'message' => '',
                'pagination' => $pagination
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        }
    }

    public function delivery_boy_orders(Request $request, $delivery_boy_id)
    {
        $orders = Orders::query()->select('id')->where('delivery_boy_id', '=', $delivery_boy_id)->where('delivery_status', '=', $request->delivery_status)->paginate();
        $pagination = $orders->toArray();
        if (!$orders->isEmpty()) {
            $order_data = [];
            foreach ($orders as $order) {
                $order_data[] = $this->get_single_order($order->id);
            }
            unset($pagination['data']);
            return response()->json([
                'data' => $order_data,
                'status' => true,
                'message' => '',
                'pagination' => $pagination
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        }
    }
    /**
     * Assigns an order to a specific delivery boy
     * @author Huzaifa Haleem
     * @version 1.1.0
     */
    public function assign_order(Request $request)
    {
        $order = Orders::find($request->order_id);
        if (!$order->isEmpty()) {
            $order->delivery_status = $request->delivery_status;
            $order->delivery_boy_id = $request->delivery_boy_id;
            $order->order_status = $request->order_status;
            $order->save();
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => config('constants.ORDER_ASSIGNED')
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        }
    }
    /**
     * Updates an assigned order
     * @author Huzaifa Haleem
     * @version 1.1.0
     */
    public function update_assign(Request $request)
    {
        $order = Orders::find($request->order_id);
        if (!$order->isEmpty()) {
            $order->delivery_status = $request->delivery_status;
            $order->delivery_boy_id = $request->delivery_boy_id;
            $order->order_status = $request->order_status;
            if ($request->order_status == 'delivered' && $request->delivery_status == 'complete') {
                $order->delivery_status = 'pending_approval';
                $user = User::find($order->seller_id);
                $user_money = $user->pending_withdraw;
                $user->pending_withdraw = $order->order_total + $user_money;
                $user->save();
                //$this->calculateDriverFair($order, $user);
            }
            $order->driver_charges = $request->driver_charges;
            $order->driver_traveled_km = $request->driver_traveled_km;
            $order->save();
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => config('constants.ORDER_UPDATED')
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        }
    }

    // public function newOld(Request $request)
    // {
    //     if ($request->has('type')) {
    //         if ($request->type == 'delivery') {
    //             $validatedData = Validator::make($request->all(), [
    //                 'receiver_name' => 'required',
    //                 'phone_number' => 'required',
    //                 'address' => 'required',
    //                 'house_no' => 'required',
    //                 'delivery_charges' => 'required',
    //                 'service_charges' => 'required'
    //             ]);
    //             if ($validatedData->fails()) {
    //                 return response()->json($validatedData->errors(), 422);
    //             }
    //         }
    //     } else {
    //         return response()->json(['type' => ['The type field is required.']], 422);
    //     }
    //     $grouped_seller = [];
    //     foreach ($request->items as $item) {
    //         $product_id = $item['product_id'];
    //         $qty = $item['qty'];
    //         $product_price = (new ProductsController())->get_product_price($product_id);
    //         $product_seller_id = (new ProductsController())->get_product_seller_id($product_id);
    //         $temp = [];
    //         $temp['qty'] = $qty;
    //         $temp['product_id'] = $product_id;
    //         $temp['price'] = $product_price;
    //         $temp['seller_id'] = $product_seller_id;
    //         $grouped_seller[$product_seller_id][] = $temp;
    //     }

    //     $count = 0;
    //     $order_arr = [];
    //     foreach ($grouped_seller as $seller_id => $order) {
    //         $user_id = Auth()->id(); 
    //         // $user_id = 306; 
    //         $order_total = 0;
    //         $total_items = 0;
    //         foreach ($order as $order_item) {
    //             $total_items = $total_items + $order_item['qty'];
    //             $order_total = $order_total + ($order_item['price'] * $order_item['qty']);
    //         }

    //         $user = User::find($seller_id);
    //         $user_money = $user->pending_withdraw;
    //         $user->pending_withdraw = $order_total + $user_money;
    //         $user->save();

    //         $new_order = new Orders();
    //         $new_order->user_id = $user_id;
    //         $new_order->order_total = $order_total;
    //         $new_order->total_items = $total_items;
    //         $new_order->type = $request->type;
    //         if ($request->type == 'delivery') {
    //             $new_order->receiver_name = $request->receiver_name;
    //             $new_order->phone_number = $request->phone_number;
    //             $new_order->address = $request->address;
    //             $new_order->house_no = $request->house_no;
    //             $new_order->flat = $request->flat;
    //             $new_order->delivery_charges = $request->delivery_charges[$count];
    //             $new_order->service_charges = $request->service_charges;
    //         }
    //         $new_order->description = $request->description;
    //         $new_order->payment_status = $request->payment_status ?? "hidden";
    //         $new_order->seller_id = $seller_id;
    //         $new_order->save();
    //         $order_id = $new_order->id;
    //         $order_arr[] = $order_id;
    //         foreach ($order as $order_item) {
    //             $new_order_item = new OrderItems();
    //             $new_order_item->order_id = $order_id;
    //             $new_order_item->product_id = $order_item['product_id'];
    //             $new_order_item->product_price = $order_item['price'];
    //             $new_order_item->product_qty = $order_item['qty'];
    //             $new_order_item->save();
    //         }
    //         $count++;
    //     }
    //     $return_data = $this->get_orders_from_ids($order_arr);
    //     $user_arr = [
    //         'data' => $return_data,
    //         'status' => true,
    //         'message' => 'Order Added Successfully'

    //     ];
    //     return response()->json($user_arr, 200);
    // }

    /**
     * Inserts a newly arrived order
     * @author Mirza Abdullah Izhar
     * @version 1.2.0
     */
    public function new(Request $request)
    {
        if ($request->has('type')) {
            if ($request->type == 'delivery') {
                $validatedData = Validator::make($request->all(), [
                    'receiver_name' => 'required',
                    'phone_number' => 'required',
                    'address' => 'required',
                    'house_no' => 'required',
                    'delivery_charges' => 'required',
                    'service_charges' => 'required'
                ]);
                if ($validatedData->fails()) {
                    return response()->json([
                        'data' => [],
                        'status' => false,
                        'message' => $validatedData->errors()
                    ], 422);
                }
            }
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => 'The type field is required.'
            ], 422);
        }
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
        $count = 0;
        $order_arr = [];
        foreach ($grouped_seller as $seller_id => $order) {
            $user_id = Auth()->id();
            // $user_id = 306;
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
            $new_order->type = $request->type;
            if ($request->type == 'delivery') {
                $new_order->receiver_name = $request->receiver_name;
                $new_order->phone_number = $request->phone_number;
                $new_order->address = $request->address;
                $new_order->house_no = $request->house_no;
                $new_order->flat = $request->flat;
                $new_order->delivery_charges = $request->delivery_charges[$count];
                $new_order->service_charges = $request->service_charges;
            }
            $new_order->description = $request->description;
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
            $count++;
        }
        return response()->json([
            'data' => $this->get_orders_from_ids($order_arr),
            'status' => true,
            'message' => 'Order added successfully.'
        ], 200);
    }

    public function updateOrder(Request $request)
    {
        $order_ids = $request->ids;
        $order_arr = explode(',', $order_ids);
        $count = 0;
        foreach ($order_arr as $order_id) {

            $order = Orders::find($order_id);
            if ($request->payment_status == "paid" && $order->payment_status != "paid" && $request->order_status == 'complete' && $order->order_status != 'complete' && $request->delivery_status == 'delivered' && $order->delivery_status != 'delivered') {
                $user = User::find($order->seller_id);
                $user_money = $user->pending_withdraw;
                $user->pending_withdraw = $order->order_total + $user_money;
                $user->save();
                //$this->calculateDriverFair($order, $user);
            }
            $order->lat = $request->lat;
            $order->lng = $request->lng;
            $order->type = $request->type;
            if ($request->type == 'delivery') {
                $order->receiver_name = $request->receiver_name;
                $order->phone_number = $request->phone_number;
                $order->address = $request->address;
                $order->house_no = $request->house_no;
                $order->flat = $request->flat;
                $order->delivery_charges = $request->delivery_charges[$count];
                $order->service_charges = $request->service_charges;
            }
            $order->description = $request->description;
            $order->payment_status = $request->payment_status;
            $order->order_status = $request->order_status;
            $order->transaction_id = $request->transaction_id;

            $order->driver_charges = $request->driver_charges;
            $order->driver_traveled_km = $request->driver_traveled_km;
            $order->save();
            $count++;
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
        $temp['order_items'] = OrderItems::query()->with('products.user')->where('order_id', '=', $order_id)->get();
        // $temp['seller'] = User::find($order->seller_id);
        return $temp;
    }

    public function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $address1 = $latitude1 . ', ' . $longitude1;
        $address2 = $latitude2 . ', ' . $longitude2;
        $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . urlencode($address1) . "&destination=" . urlencode($address2) . "&key=AIzaSyBFDmGYlVksc--o1jpEXf9jVQrhwmGPxkM";
        $query = file_get_contents($url);
        $results = json_decode($query, true);
        $distanceString = explode(' ', $results['routes'][0]['legs'][0]['distance']['text']);
        $kms = (int)$distanceString[0] * 0.621371;
        return $kms > 1 ? $kms : 1;
    }

    public function calculateDriverFair($order, $store)
    {
        $childOrders = Orders::where('delivery_boy_id', $order->delivery_boy_id)
            ->where('id', '!=', $order->id)
            ->where('order_status', 'onTheWay')->get();
        if (count($childOrders) > 0) {
            foreach ($childOrders as $childOrder) {
                $childOrder->update(['parent_id' => $order->id]);
            }
        }
        $driver = User::find($order->delivery_boy_id);
        $driver_money = $driver->pending_withdraw;
        $fair_per_mile = 1.50;
        $pickup = 1.50;
        $drop_off = 1.10;
        $fee = 0.20;
        if (is_null($order->parent_id)) {
            $distance = $this->getDistanceBetweenPointsNew($order->lat, $order->lng, $store->lat, $store->lon);
            $totalFair = ($distance * $fair_per_mile) + $pickup + $drop_off;
            $teekitCharges = $totalFair * $fee;
            $driver->pending_withdraw = ($totalFair - $teekitCharges) + $driver_money;
            $driver->save();
            $order->driver_charges = $totalFair - $fee;
            $order->driver_traveled_km = (round(($distance * 1.609344), 2));
            $order->save();
        } else {
            $oldOrder = Orders::find($order->parent_id);
            $distance = $this->getDistanceBetweenPointsNew($order->lat, $order->lng, $oldOrder->lat, $oldOrder->lon);
            $pickup_val = $oldOrder->seller_id == $order->seller_id ? 0.0 : $pickup;
            $totalFair = ($distance * $fair_per_mile) + $drop_off + $pickup_val;
            $teekitCharges = $totalFair * $fee;
            $driver->pending_withdraw = ($totalFair - $teekitCharges) + $driver_money;
            $driver->save();
        }
    }
}
