<?php

namespace App\Http\Controllers;

use App\Categories;
use App\Mail\OrderIsReadyMail;
use App\Mail\StoreRegisterMail;
use App\OrderItems;
use App\Orders;
use App\Pages;
use App\productImages;
use App\Products;
use App\User;
use App\WithdrawalRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->hasRole('seller')) {

            $pending_orders = Orders::query()->where('order_status', '=', 'ready')->where('seller_id', '=', Auth::id())->count();
            $total_orders = Orders::query()->where('payment_status', '!=', 'hidden')->where('seller_id', '=', Auth::id())->count();
            $total_products = Products::query()->where('user_id', '=', Auth::id())->count();
            $total_sales = Orders::query()->where('payment_status', '=', 'paid')->where('seller_id', '=', Auth::id())->sum('order_total');
            $all_orders = Orders::where('seller_id', \auth()->id())
                ->whereNotNull('order_status')
                ->orderby(\DB::raw('case when is_viewed= 0 then 0 when order_status= "pending" then 1 when order_status= "ready" then 2 when order_status= "assigned" then 3
                 when order_status= "onTheWay" then 4 when order_status= "delivered" then 5 end'))
                ->simplePaginate(5);
//                ->get();
//            dd($all_orders);
            return view('shopkeeper.dashboard', compact('pending_orders', 'total_products', 'total_orders', 'total_sales', 'all_orders'));
        } else {

            return $this->admin_home();
        }
    }

    public function inventory(Request $request)
    {
        if (Auth::user()->hasRole('seller')) {
            $inventory = Products::query()->where('user_id', '=', Auth::id());
            if ($request->search) {
                $inventory = $inventory->where('product_name', 'LIKE', $request->search);
            }
            if ($request->category) {
                $inventory = $inventory->where('category_id', '=', $request->category);
            }
            $categories = Categories::all();
            $inventory = $inventory->paginate(9);
            $inventory_p = $inventory;
            $inventories = [];
            foreach ($inventory as $in) {
                $inventories[] = Products::get_product_info($in->id);
            }
//        Auth::user()->hasRole('seller');
            return view('shopkeeper.inventory.list', compact('inventories', 'inventory_p', 'categories'));
        } else {
            abort(404);
        }
    }

    public function inventory_edit($product_id)
    {
        if (Auth::user()->hasRole('seller')) {
            $invent = Products::query()->where('user_id', '=', Auth::id())->where('id', '=', $product_id);
            if (empty($invent)) {
                abort(404);
            }
            $categories = Categories::all();
            $inventory = Products::get_product_info($product_id);
            return view('shopkeeper.inventory.edit', compact('inventory', 'categories'));
        } else {
            abort(404);
        }
    }

    public function inventory_add(Request $request)
    {
        if (Auth::user()->hasRole('seller')) {
            $categories = Categories::all();
            $inventory = new Products();
            return view('shopkeeper.inventory.add', compact('inventory', 'categories'));
        } else {
            abort(404);
        }
    }

    public function delete_img($image_id)
    {
        if (Auth::user()->hasRole('seller')) {
            productImages::find($image_id)->delete();
            return redirect()->back();
        } else {
            abort(404);
        }
    }

    public function inventory_disable($product_id)
    {
        $product = Products::find($product_id);
        $product->status = 0;
        $product->qty = 0;
        $product->save();
        flash('Product Disabled Successfully')->success();
        return Redirect::back();
    }

    public function inventory_enable($product_id)
    {
        $product = Products::find($product_id);
        $product->status = 1;
        $product->save();
        flash('Product Enabled Successfully')->success();
        return Redirect::back();
    }

    public function inventory_update(Request $request, $product_id)
    {
        if (Auth::user()->hasRole('seller')) {
            $data = $request->all();
            unset($data['_token']);
            if ($request->has('colors')){
                $keys = $data['colors'];
                unset($data['color']);
                $a = array_fill_keys($keys, true);
                $data['colors'] = json_encode($a);
            }else{
                $data['colors'] = null;
            }

            if (!isset($data['van'])) {
                $data['van'] = 0;
            }
            if (!isset($data['bike'])) {
                $data['bike'] = 0;
            }
//        print_r($data);die;


            unset($data['gallery']);


            $product = Products::find($product_id);
            if (!empty($product)) {


                $filename = $product->feature_img;
                if ($request->hasFile('feature_img')) {
                    $file = $request->file('feature_img');
                    $filename = uniqid($product->id . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
                    Storage::disk('user_public')->put($filename, File::get($file));
                    if (Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                        info("file is store successfully : " . $filename);
                        $filename = "/user_imgs/" . $filename;
                    } else {
                        info("file is not found :- " . $filename);
                    }


                }


                $data['feature_img'] = $filename;

                $user_id = Auth::id();

                if ($request->hasFile('gallery')) {
                    $images = $request->file('gallery');
                    foreach ($images as $image) {


                        $file = $image;
                        $filename = uniqid($user_id . "_" . $product->id . "_" . $product->product_name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
                        Storage::disk('user_public')->put($filename, File::get($file));
                        if (Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                            info("file is store successfully : " . $filename);
                            $filename = "/user_imgs/" . $filename;
                        } else {
                            info("file is not found :- " . $filename);
                        }

                        $product_images = new productImages();
                        $product_images->product_id = $product->id;
                        $product_images->product_image = $filename;
                        $product_images->save();
                    }
                }


                foreach ($data as $key => $value) {
                    $product->$key = $value;
                }
                $product->save();
                flash('Inventory updated successfully.')->success();
                return \redirect()->route('inventory');
            }
        } else {
            abort(404);
        }

    }

    public function user_img_update(Request $request)
    {

        $user = User::find(\auth()->id());
        $filename = \auth()->user()->name;
        if ($request->hasFile('user_img')) {
            $file = $request->file('user_img');
            $filename = uniqid($user->id . '_' . $user->name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
            Storage::disk('user_public')->put($filename, File::get($file));
            if (Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                info("file is store successfully : " . $filename);
                $filename = "/user_imgs/" . $filename;
            } else {
                info("file is not found :- " . $filename);
            }


        }
        $user->user_img = $filename;
        $user->save();

        flash('Store Image Successfully Updated')->success();
        return Redirect::back();
    }

    public function inventory_adddb(Request $request)
    {
        if (Auth::user()->hasRole('seller')) {

            $data = $request->all();
            unset($data['_token']);
            if ($request->has('colors')){
                $keys = $data['colors'];
                unset($data['color']);
                $a = array_fill_keys($keys, true);
                $data['colors'] = json_encode($a);
            }

            if (!isset($data['van'])) {
                $data['van'] = 0;
            }
            if (!isset($data['bike'])) {
                $data['bike'] = 0;
            }

            unset($data['gallery']);


            $user_id = Auth::id();
            $data['user_id'] = $user_id;
            $product = new Products();
            if (!empty($product)) {


                $filename = $product->feature_img;
                if ($request->hasFile('feature_img')) {
                    $file = $request->file('feature_img');
                    $filename = uniqid($user_id . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
                    Storage::disk('user_public')->put($filename, File::get($file));
                    if (Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                        info("file is store successfully : " . $filename);
                        $filename = "/user_imgs/" . $filename;
                    } else {
                        info("file is not found :- " . $filename);
                    }


                }


                $data['feature_img'] = $filename;

                foreach ($data as $key => $value) {
                    $product->$key = $value;
                }
                $product->save();
                if ($request->hasFile('gallery')) {
                    $images = $request->file('gallery');
                    foreach ($images as $image) {


                        $file = $image;
                        $filename = uniqid($user_id . "_" . $product->id . "_" . $product->product_name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
                        Storage::disk('user_public')->put($filename, File::get($file));
                        if (Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                            info("file is store successfully : " . $filename);
                            $filename = "/user_imgs/" . $filename;
                        } else {
                            info("file is not found :- " . $filename);
                        }

                        $product_images = new productImages();
                        $product_images->product_id = $product->id;
                        $product_images->product_image = $filename;
                        $product_images->save();
                    }
                }
                flash('Inventory added successfully.')->success();
                return \redirect()->route('inventory');
            }
        } else {
            abort(404);
        }


    }

    public function payment_settings()
    {
        $payment_settings = User::find(Auth::id())->bank_details;
        return view('shopkeeper.settings.payment', compact('payment_settings'));
    }

    public function general_settings()
    {
        $user = User::find(Auth::id());
        $business_hours = $user->business_hours;
        $address = $user->address_1;
        $business_location = $user->business_location;
        return view('shopkeeper.settings.general', compact('business_hours', 'address', 'business_location'));
    }

    public function time_update(Request $request)
    {
        $data = $request->time;
//        business_hours
        $user = User::find(Auth::id());
        $user->business_hours = json_encode($data);
        $user->save();
        flash('Business Hours Updated');
        return redirect()->back();
    }

    public function location_update(Request $request)
    {
        $data = $request->Address;
        $location = $request->location_text;
        $user = User::find(Auth::id());
        $user->business_location = json_encode($data);
        $user->address_1 = $location;
        $user->lat = $data['lat'];
        $user->lon = $data['long'];
        $user->save();
        flash('Location Updated');
        return redirect()->back();
    }

    public function payment_settings_update(Request $request)
    {
        $data = $request->all();
        if (empty($data['bank'][2]['bank_name']) || empty($data['bank'][2]['account_number']) || empty($data['bank'][2]['branch'])) {
            unset($data['bank'][2]);
        }
        unset($data['_token']);
        $data = $data['bank'];
        print_r($data);
        $user = User::find(Auth::id());
        $user->bank_details = json_encode($data);
        $user->save();
        flash('Bank Details Updated');
        return redirect()->back();
    }

    public function orders(Request $request)
    {
//        $inventory = Products::query()->where('user_id','=',Auth::id())->paginate(9);
//        $inventory_p = $inventory;
//        $inventories = [];
//        foreach ($inventory as $in){
//            $inventories[] = Products::get_product_info($in->id);
//        }

        $return_arr = [];
        $orders = Orders::query()->where('seller_id', '=', Auth::id())->where('payment_status', '!=', 'hidden')->orderByDesc('id');
        if ($request->search) {
            $order = Orders::find($request->search);
            $order->is_viewed = 1;
            $order->save();
            $orders = $orders->where('id', '=', $request->search);
        }
        $orders = $orders->paginate(10);
        $orders_p = $orders;

        foreach ($orders as $order) {
//            $order_items = [];
            $items = OrderItems::query()->where('order_id', '=', $order->id)->get();
            $item_arr = [];
            foreach ($items as $item) {
                $product = (new ProductsController())->get_product_info($item->product_id);
                $item['product'] = $product;
                $item_arr[] = $item;
            }
            $order['items'] = $item_arr;
            $return_arr[] = $order;
        }
//        Auth::user()->hasRole('seller');
//        echo "<pre>";
//        print_r($return_arr);
        $orders = $return_arr;
        return view('shopkeeper.orders.list', compact('orders', 'orders_p'));
    }


    function csvToJson($fname)
    {
        // open csv file
        if (!($fp = fopen($fname, 'r'))) {
            die("Can't open file...");
        }

        //read csv headers
        $key = fgetcsv($fp, "1024", ",");

        // parse csv rows into array
        $json = array();
        while ($row = fgetcsv($fp, "1024", ",")) {
            $json[] = array_combine($key, $row);
        }

        // release file handle
        fclose($fp);

        // encode array to json
        return json_encode($json);
    }

    public function importProducts(Request $request)
    {
        $user_id = Auth::id();

        if ($request->hasFile('file')) {
            $import_data = json_decode($this->csvToJson($request->file('file')), true);

            foreach ($import_data as $p) {
                if (isset($p['images'])) {
                    $images = explode(',', $p['images']);
                    unset($p['images']);
                }
                if (is_array($p))
                    $ppt = array_keys($p);
                $product = new Products();
                foreach ($ppt as $t) {
                    if ($t == 'colors') {
                        $colors = explode(',', $p[$t]);
                        $product->$t = json_encode(array_fill_keys($colors, true));
                    } elseif (($t == 'bike' && $p[$t] == null) || ($t == 'van' && $p[$t] == null)) {
                        $product->$t = 0;
                    } else {
                        $product->$t = $p[$t];
                    }
                }

                $product->user_id = $user_id;
                $product->save();
                $p_id = $product->id;
                if (isset($images)) {
                    foreach ($images as $image) {
                        $product_images = new productImages();
                        $product_images->product_id = (int)$p_id;
                        $product_images->product_image = $image;
                        $product_images->save();
                    }
                }
            }

            flash('Importing Complete');
        }
        return redirect()->back();
    }

    public function change_order_status($order_id)
    {
        $order = Orders::where('id', '=', $order_id)->first();
        $user = $order->user;
        $order->update(['order_status' => 'ready', 'is_viewed' => 1]);
        Mail::to($user->email)
            ->send(new OrderIsReadyMail($order));
        return Redirect::back();
    }

    public function admin_home()
    {

        if (Auth::user()->hasRole('superadmin')) {

            $terms_page = Pages::query()->where('page_type', '=', 'terms')->first();
            $help_page = Pages::query()->where('page_type', '=', 'help')->first();
            $faq_page = Pages::query()->where('page_type', '=', 'faq')->first();
            $slogan = Pages::query()->where('page_type', '=', 'slogan')->first();
            $favicon = Pages::query()->where('page_type', '=', 'favicon')->first();
            $logo = Pages::query()->where('page_type', '=', 'logo')->first();

            $pending_orders = Orders::query()->where('order_status', '=', 'ready')->count();
            $total_products = Orders::query()->where('payment_status', '!=', 'hidden')->count();
            $total_orders = Products::query()->count();
            $total_sales = Orders::query()->where('payment_status', '=', 'paid')->sum('order_total');
            return view('admin.home', compact('terms_page', 'help_page', 'faq_page', 'slogan', 'favicon', 'logo', 'pending_orders', 'total_products', 'total_orders', 'total_sales'));
        } else {
            abort(404);
        }
    }

    public function asetting()
    {

        if (Auth::user()->hasRole('superadmin')) {

            $terms_page = Pages::query()->where('page_type', '=', 'terms')->first();
            $help_page = Pages::query()->where('page_type', '=', 'help')->first();
            $faq_page = Pages::query()->where('page_type', '=', 'faq')->first();
            $slogan = Pages::query()->where('page_type', '=', 'slogan')->first();
            $favicon = Pages::query()->where('page_type', '=', 'favicon')->first();
            $logo = Pages::query()->where('page_type', '=', 'logo')->first();

            $pending_orders = Orders::query()->where('order_status', '=', 'ready')->count();
            $total_products = Orders::query()->where('payment_status', '!=', 'hidden')->count();
            $total_orders = Products::query()->count();
            $total_sales = Orders::query()->where('payment_status', '=', 'paid')->sum('order_total');
            return view('admin.settings', compact('terms_page', 'help_page', 'faq_page', 'slogan', 'favicon', 'logo', 'pending_orders', 'total_products', 'total_orders', 'total_sales'));
        } else {
            abort(404);
        }
    }

    public function admin_customer_details($user_id)
    {
        $return_arr = [];

        if (Auth::user()->hasRole('superadmin')) {
            $user = User::find($user_id);
            if ($user->hasRole('seller')) {
                $orders = Orders::query()->where('seller_id', '=', $user_id);
            }
            if ($user->hasRole('buyer')) {
                $orders = Orders::query()->where('user_id', '=', $user_id);
            }
            if ($user->hasRole('delivery_boy')) {
                $orders = Orders::query()->where('delivery_boy_id', '=', $user_id);
            }
            $orders = $orders->where('payment_status', '!=', 'hidden')->orderByDesc('id');

            $orders = $orders->paginate(10);
            $orders_p = $orders;

            foreach ($orders as $order) {
//            $order_items = [];
                $items = OrderItems::query()->where('order_id', '=', $order->id)->get();
                $item_arr = [];
                foreach ($items as $item) {
                    $product = (new ProductsController())->get_product_info($item->product_id);
                    $item['product'] = $product;
                    $item_arr[] = $item;
                }
                $order['items'] = $item_arr;
                $return_arr[] = $order;
            }
//        Auth::user()->hasRole('seller');
//        echo "<pre>";
//        print_r($return_arr);
            $orders = $return_arr;
            return view('admin.customer_details', compact('orders', 'orders_p', 'user'));
        } else {
            abort(401);
        }
    }


    public function all_cat()
    {


        $categories = Categories::paginate();
        //echo"work";die;
        return view('admin.categories', compact('categories'));

    }


    public function add_cat(Request $request)
    {
        // $validate = Categories::validator($request);

        // if ($validate->fails()) {
        //     $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
        //     return response()->json($response, 400);
        // }
        $category = new Categories();
        $category->category_name = $request->category_name;


        if ($request->hasFile('category_image')) {
            $image = $request->file('category_image');

            $file = $image;

            $cat_name = str_replace(' ', '_', $category->category_name);
            $filename = uniqid("Category_" . $cat_name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
            Storage::disk('user_public')->put($filename, File::get($file));
            if (Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                info("file is store successfully : " . $filename);
                $filename = "/user_imgs/" . $filename;
            } else {
                info("file is not found :- " . $filename);
            }


            $category->category_image = $filename;
        }

        $category->save();

        flash('Added')->success();
        return Redirect::back();
    }

    public function update_cat(Request $request, $id)
    {
        //$validate = Categories::updateValidator($request);


        // if ($validate->fails()) {
        //     $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
        //     return response()->json($response, 400);
        // }
        $category = Categories::find($id);
        $category->category_name = $request->category_name;


        if ($request->hasFile('category_image')) {
            $image = $request->file('category_image');

            $file = $image;
            $cat_name = str_replace(' ', '_', $category->category_name);
            $filename = uniqid("Category_" . $cat_name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
            Storage::disk('user_public')->put($filename, File::get($file));
            if (Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                info("file is store successfully : " . $filename);
                $filename = "/user_imgs/" . $filename;
            } else {
                info("file is not found :- " . $filename);
            }


            $category->category_image = $filename;
        }

        $category->save();
        flash('Updated')->success();
        return Redirect::back();
        //$response = array('status' => true, 'message' => 'Category', 'data' => $category);
        //echo response()->json($response, 200);
    }


    public function update_pages(Request $request)
    {
        if (Auth::user()->hasRole('superadmin')) {
            // echo "oK";
            $terms_page = Pages::query()->where('page_type', '=', 'terms')->update(['page_content' => $request->tos]);
            $help_page = Pages::query()->where('page_type', '=', 'help')->update(['page_content' => $request->help]);
            $faq_page = Pages::query()->where('page_type', '=', 'faq')->update(['page_content' => $request->faq]);
            return Redirect::back();
        } else {
            abort(404);
        }
    }


    public function admin_stores(Request $request)
    {

        if (Auth::user()->hasRole('superadmin')) {
            $users = User::query()->whereHas('roles', function ($query) {
                $query->where('role_id', 2);
            });

            if ($request->search) {
                $users = $users->where('business_name', 'LIKE', $request->search);
            }

            $users = $users->paginate(10);
            return view('admin.stores', compact('users'));
        } else {
            abort(404);
        }
    }

    public function admin_customers(Request $request)
    {

        if (Auth::user()->hasRole('superadmin')) {
            $users = User::query()->whereHas('roles', function ($query) {
                $query->where('role_id', 3);
            });

            if ($request->search) {
                $users = $users->where('name', 'LIKE', $request->search);
            }

            $users = $users->paginate(10);
            return view('admin.customers', compact('users'));
        } else {
            abort(404);
        }
    }

    public function admin_drivers(Request $request)
    {

        if (Auth::user()->hasRole('superadmin')) {
            $users = User::query()->whereHas('roles', function ($query) {
                $query->where('role_id', 4);
            });

            if ($request->search) {
                $users = $users->where('name', 'LIKE', $request->search);
            }

            $users = $users->paginate(10);

            return view('admin.drivers', compact('users'));
        } else {
            abort(404);
        }
    }

    public function admin_orders(Request $request)
    {

        if (Auth::user()->hasRole('superadmin')) {
            $return_arr = [];
            $orders = Orders::query()->where('payment_status', '!=', 'hidden')->orderByDesc('id');
            if ($request->search) {
                $orders = $orders->where('id', '=', $request->search);
            }
            if ($request->user_id) {
                $orders = $orders->where('user_id', '=', $request->user_id);
            }
            if ($request->store_id) {
                $orders = $orders->where('seller_id', '=', $request->store_id);
            }
            $orders = $orders->paginate(10);
            $orders_p = $orders;

            foreach ($orders as $order) {
//            $order_items = [];
                $items = OrderItems::query()->where('order_id', '=', $order->id)->get();
                $item_arr = [];
                foreach ($items as $item) {
                    $product = (new ProductsController())->get_product_info($item->product_id);
                    $item['product'] = $product;
                    $item_arr[] = $item;
                }
                $order['items'] = $item_arr;
                $return_arr[] = $order;
            }
//        Auth::user()->hasRole('seller');
//        echo "<pre>";
//        print_r($return_arr);
            $orders = $return_arr;
            return view('admin.orders', compact('orders', 'orders_p'));
//            return view('admin.orders');
        } else {
            abort(404);
        }
    }


    public function withdrawals()
    {
        if (Auth::user()->hasRole('seller')) {
            $user_id = Auth::id();
            $return_data = WithdrawalRequests::query()->where('user_id', '=', $user_id)->get();
            $transactions = $return_data;
            return view('shopkeeper.withdrawal', compact('transactions'));
        }
        if (Auth::user()->hasRole('superadmin')) {
            $user_id = Auth::id();
            $return_data = WithdrawalRequests::has('user.seller')->get();
            $transactions = $return_data;
            return view('admin.withdrawal', compact('transactions'));
        }
    }

    public function withdrawalDrivers()
    {
        if (Auth::user()->hasRole('seller')) {
            $user_id = Auth::id();
            $return_data = WithdrawalRequests::query()->where('user_id', '=', $user_id)->get();
            $transactions = $return_data;
            return view('shopkeeper.withdrawal', compact('transactions'));
        }
        if (Auth::user()->hasRole('superadmin')) {
            $transactions = WithdrawalRequests::has('user.driver')->get();
            return view('admin.withdrawal-drivers', compact('transactions'));
        }
    }

    public function withdrawals_request(Request $request)
    {
        if (Auth::user()->hasRole('seller')) {
            if (auth()->user()->pending_withdraw < $request->amount) {
                flash('Please Choose Correct Value')->error();
            } else {
                $user = User::find(\auth()->id());
                $user->pending_withdraw = $user->pending_withdraw - $request->amount;
                $user->total_withdraw = $user->total_withdraw + $request->amount;
                $with = new WithdrawalRequests();
                $with->user_id = \auth()->id();
                $with->amount = $request->amount;
                $with->status = 'Pending';
                if (empty($user->bank_details)) {
                    flash('Update Bank Info')->error();
                    return Redirect::back();
                }
                $with->bank_detail = $user->bank_details;
                $with->save();
                $user->save();

                flash('Request Sent')->success();
            }
            return Redirect::back();
//            $user_id = Auth::id();
//            $return_data = WithdrawalRequests::query()->where('user_id','=',$user_id)->get();
//            $transactions =$return_data;
//            return view('shopkeeper.withdrawal', compact('transactions'));
        }
        if (Auth::user()->hasRole('superadmin')) {
            $with = WithdrawalRequests::find($request->id);
            $with->status = $request->status;
            $with->transaction_id = $request->t_id;
            $with->save();
            flash('Updated')->success();
            return Redirect::back();
        }
    }

    public function change_user_status($user_id, $status)
    {
//        echo $user_id;
//        echo $status;
        User::query()->where('id', '=', $user_id)->update(['is_active' => $status]);
        if ($status == 1) {
            $user = User::findOrFail($user_id);
            $html = '<html>
            Hi, ' . $user->name . '<br><br>

            Thank you for registering on ' . env('APP_NAME') . '.

<br>
            Your store has been approved. Please login to the
            <a href="' . env('FRONTEND_URL') . '">Store</a> to update your store
<br><br><br>
        </html>';

            $subject = env('APP_NAME') . ': Account Approved!';
            Mail::to($user->email)
                ->send(new StoreRegisterMail($html, $subject));
        }
        return Redirect::back();
    }

    public function admin_queries()
    {

        if (Auth::user()->hasRole('superadmin')) {

            return view('admin.queries');
        } else {
            abort(404);
        }
    }

    public function my_order_count()
    {
//        Auth::user()->hasRole('seller');
        if (Auth::user()->hasRole('seller')) {

//            $pending_orders = Orders::query()->where('order_status','=','ready')->where('seller_id','=',Auth::id())->count();
//            $total_products = Orders::query()->where('payment_status','!=','hidden')->where('seller_id','=',Auth::id())->count();
            $total_orders = Orders::query()->where('seller_id', '=', Auth::id())->count();
//            $total_sales = Orders::query()->where('payment_status','=','paid')->where('seller_id','=',Auth::id())->sum('order_total');
//            return $this->inventory();
            echo $total_orders;
            // return view('shopkeeper.dashboard',compact('pending_orders','total_products','total_orders','total_sales'));
        } else {

        }
    }


}
