<?php

namespace App\Http\Controllers;

use App\Categories;
use App\Mail\OrderIsCanceledMail;
use App\Mail\OrderIsCompletedMail;
use App\Mail\OrderIsReadyMail;
use App\Mail\StoreRegisterMail;
use App\OrderItems;
use App\Orders;
use App\Pages;
use App\productImages;
use App\Products;
use App\Services\TwilioSmsService;
use App\User;
use App\VerificationCodes;
use App\WithdrawalRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Stripe;

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
            $user = User::query()->where('id', '=', Auth::id())->get();
            $pending_orders = Orders::query()->where('order_status', '=', 'pending')->where('seller_id', '=', Auth::id())->count();
            $total_orders = Orders::query()->where('payment_status', '!=', 'hidden')->where('seller_id', '=', Auth::id())->count();
            $total_products = Products::query()->where('user_id', '=', Auth::id())->count();
            $total_sales = Orders::query()->where('payment_status', '=', 'paid')->where('seller_id', '=', Auth::id())->sum('order_total');
            $all_orders = Orders::where('seller_id', \auth()->id())
                ->whereNotNull('order_status')
                ->orderby(\DB::raw('case when is_viewed= 0 then 0 when order_status= "pending" then 1 when order_status= "ready" then 2 when order_status= "assigned" then 3
                 when order_status= "onTheWay" then 4 when order_status= "delivered" then 5 end'))
                ->simplePaginate(5);
            return view('shopkeeper.dashboard', compact('user', 'pending_orders', 'total_products', 'total_orders', 'total_sales', 'all_orders'));
        } else {
            return $this->admin_home();
        }
    }

    public function inventory(Request $request)
    {
        if (Auth::user()->hasRole('seller')) {
            $inventory = Products::query()->where('user_id', '=', Auth::id())->orderBy('id', 'DESC');
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

    public function inventory_add_bulk(Request $request)
    {
        if (Auth::user()->hasRole('seller')) {
            return view('shopkeeper.inventory.add_bulk');
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
    /**
     * Disable's a single product
     * @author Huzaifa Haleem
     * @version 1.1.0
     */
    public function inventory_disable($product_id)
    {
        $product = Products::find($product_id);
        $product->status = 0;
        // $product->qty = 0;
        $product->save();
        flash('Product Disabled Successfully')->success();
        return Redirect::back();
    }
    /**
     * Enable's a single product
     * @author Huzaifa Haleem
     * @version 1.1.0
     */
    public function inventory_enable($product_id)
    {
        $product = Products::find($product_id);
        $product->status = 1;
        $product->save();
        flash('Product Enabled Successfully')->success();
        return Redirect::back();
    }
    /**
     * Disable's all products of logged-in user
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function inventory_disable_all(Request $request)
    {
        DB::table('products')
            ->where('user_id', Auth::id())
            ->update(['status' => 0]);
        flash('All Products Disabled Successfully')->success();
        return Redirect::back();
    }
    /**
     * Enable's all products of logged-in user
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function inventory_enable_all(Request $request)
    {
        DB::table('products')
            ->where('user_id', Auth::id())
            ->update(['status' => 1]);
        flash('All Products Enabled Successfully')->success();
        return Redirect::back();
    }
    /**
     * Inserts a single store product
     * @author Mirza Abdullah Izhar
     * @version 1.2.0
     */
    public function inventory_add_db(Request $request)
    {
        if (Auth::user()->hasRole('seller')) {
            $validatedData = Validator::make($request->all(), [
                'product_name' => 'required',
                'sku' => 'required',
                'category_id' => 'required',
                'qty' => 'required',
                'price' => 'required',
                'height' => 'required',
                'width' => 'required',
                'length' => 'required',
                'weight' => 'required',
                'status' => 'required',
                'contact' => 'required|min:10|max:10',
                'gallery' => 'required',
                'feature_img' => 'required',
                'vehicle' => 'required'
            ]);
            if ($validatedData->fails()) {
                flash('Error in adding the product because some required field is missing or invalid data.')->error();
                return Redirect::back()->withInput($request->input());
            }
            $data = $request->all();
            unset($data['_token']);
            if ($request->has('colors')) {
                $keys = $data['colors'];
                unset($data['color']);
                $a = array_fill_keys($keys, true);
                $data['colors'] = json_encode($a);
            }
            $data['bike'] = ($data['vehicle'] == 'bike') ? 1 : 0;
            $data['car'] = ($data['vehicle'] == 'car') ? 1 : 0;
            $data['van'] = ($data['vehicle'] == 'van') ? 1 : 0;
            $data['discount_percentage'] = (!isset($data['discount_percentage'])) ? 0.00 : $data['discount_percentage'];
            unset($data['gallery']);
            $user_id = Auth::id();
            $data['user_id'] = $user_id;
            $product = new Products();
            if (!empty($product)) {
                $filename = $product->feature_img;
                if ($request->hasFile('feature_img')) {
                    $file = $request->file('feature_img');
                    $filename = uniqid($user_id . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
                    // Storage::disk('spaces')->put($filename, File::get($file));
                    Storage::disk('spaces')->put($filename, File::get($file));
                    // Storage::disk('spaces')->exists($filename)
                    if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
                        info("file is store successfully : " . $filename);
                    } else {
                        info("file is not found :- " . $filename);
                    }
                }
                $data['feature_img'] = $filename;
                foreach ($data as $key => $value) {
                    if ($key == 'vehicle') continue;
                    $product->$key = ($key == 'contact') ? '+44' . $value : $value;
                }
                $product->save();
                if ($request->hasFile('gallery')) {
                    $images = $request->file('gallery');
                    foreach ($images as $image) {
                        $file = $image;
                        $filename = uniqid($user_id . "_" . $product->id . "_") . "." . $file->getClientOriginalExtension(); //create unique file name...
                        // Storage::disk('spaces')->put($filename, File::get($file));
                        Storage::disk('spaces')->put($filename, File::get($file));
                        // Storage::disk('spaces')->exists($filename)
                        if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
                            info("file is store successfully : " . $filename);
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
    /**
     * It updates a single product
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function inventory_update(Request $request, $product_id)
    {
        if (Auth::user()->hasRole('seller')) {
            $validatedData = Validator::make($request->all(), [
                'product_name' => 'required',
                'sku' => 'required',
                'category_id' => 'required',
                'qty' => 'required',
                'price' => 'required',
                'height' => 'required',
                'width' => 'required',
                'length' => 'required',
                'weight' => 'required',
                'status' => 'required',
                'contact' => 'required|min:10|max:10',
                'vehicle' => 'required'
            ]);
            if ($validatedData->fails()) {
                flash('Error in updating the product because some required field is missing or invalid data.')->error();
                return Redirect::back()->withInput($request->input());
            }
            $data = $request->all();
            unset($data['_token']);
            if ($request->has('colors')) {
                $keys = $data['colors'];
                unset($data['color']);
                $a = array_fill_keys($keys, true);
                $data['colors'] = json_encode($a);
            } else {
                $data['colors'] = null;
            }
            $data['bike'] = ($data['vehicle'] == 'bike') ? 1 : 0;
            $data['car'] = ($data['vehicle'] == 'car') ? 1 : 0;
            $data['van'] = ($data['vehicle'] == 'van') ? 1 : 0;
            $data['discount_percentage'] = (!isset($data['discount_percentage'])) ? 0.00 : $data['discount_percentage'];
            unset($data['gallery']);
            $product = Products::find($product_id);
            if (!empty($product)) {
                $filename = $product->feature_img;
                if ($request->hasFile('feature_img')) {
                    $file = $request->file('feature_img');
                    $filename = uniqid($product->id . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
                    Storage::disk('spaces')->put($filename, File::get($file));
                    if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
                        info("file is store successfully : " . $filename);
                        // $filename = "/user_imgs/" . $filename;
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
                        $filename = uniqid($user_id . "_" . $product->id . "_") . "." . $file->getClientOriginalExtension(); //create unique file name...
                        Storage::disk('spaces')->put($filename, File::get($file));
                        if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
                            info("file is store successfully : " . $filename);
                            // $filename = "/user_imgs/" . $filename;
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
                    if ($key == 'vehicle') continue;
                    $product->$key = ($key == 'contact') ? '+44' . $value : $value;
                }
                $product->save();
                flash('Inventory updated successfully.')->success();
                return \redirect()->route('inventory');
            }
        } else {
            abort(404);
        }
    }
    /**
     * It updates/uploads user image
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function user_img_update(Request $request)
    {
        $user = User::find(\auth()->id());
        $filename = \auth()->user()->name;
        if ($request->hasFile('user_img')) {
            $file = $request->file('user_img');
            $filename = uniqid($user->id . '_' . $user->name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
            Storage::disk('spaces')->put($filename, File::get($file));
            if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
                info("file is store successfully : " . $filename);
                // $filename = "/user_imgs/" . $filename;
            } else {
                info("file is not found :- " . $filename);
            }
        }
        $user->user_img = $filename;
        $user->save();
        flash('Store Image Successfully Updated')->success();
        return Redirect::back();
    }
    /**
     * Changes user setting provided in the parameter
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function change_settings(Request $request)
    {
        User::where('id', '=', Auth::id())->update(['settings->' . $request->setting_name => $request->value]);
        return \redirect()->route('home');
    }
    /**
     * Display's payment view
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function payment_settings()
    {
        $payment_settings = User::find(Auth::id())->bank_details;
        return view('shopkeeper.settings.payment', compact('payment_settings'));
    }
    /**
     * Display's store general settings
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function general_settings()
    {
        $user = User::find(Auth::id());
        $business_hours = $user->business_hours;
        $address = $user->address_1;
        $business_location = $user->business_location;
        return view('shopkeeper.settings.general', compact('business_hours', 'address', 'business_location'));
    }
    /**
     * Update's business hours of a store
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function time_update(Request $request)
    {   //dd($request->time);
        $time = $request->time;
        foreach ($time as $key => $value) {
            if (!in_array("on", $time[$key]))
                $time[$key] += ["closed" => null];
        }
        $data['time'] = $time;
        $user = User::find(Auth::id());
        $user->business_hours = json_encode($data);
        $user->save();
        flash('Business Hours Updated');
        return redirect()->back();
    }
    /**
     * Update's user location
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
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
    /**
     * Update's payment settings
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function payment_settings_update(Request $request)
    {
        $data = $request->all();
        if (empty($data['bank']['two']['bank_name']) || empty($data['bank']['two']['account_number']) || empty($data['bank']['two']['branch'])) {
            unset($data['bank']['two']);
        }
        unset($data['_token']);
        $data = $data['bank'];
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
        // $orders = Orders::query()->where('seller_id', '=', Auth::id())->where('payment_status', '!=', 'hidden')->orderByDesc('id');
        $orders = Orders::query()->where('seller_id', '=', Auth::id())->orderByDesc('id');
        if ($request->search) {
            $order = Orders::find($request->search);
            $order->is_viewed = 1;
            $order->save();
            $orders = $orders->where('id', '=', $request->search);
        }
        $orders = $orders->paginate(10);
        $orders_p = $orders;

        foreach ($orders as $order) {
            //$order_items = [];
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
        $orders = $return_arr;
        return view('shopkeeper.orders.list', compact('orders', 'orders_p'));
    }
    /**
     * Convert's CSV file to JSON
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function csvToJson($fname)
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
    /**
     * Upload's bulk products
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function importProducts(Request $request)
    {
        $user_id = Auth::id();
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension(); //Get extension of uploaded file
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize(); //Get size of uploaded file in bytes

            //Check for file extension and size
            // $this->checkUploadedFileProperties($extension, $fileSize);

            //Where uploaded file will be stored on the server 
            $location = public_path('upload/csv');
            // Upload file
            $file->move($location, $filename);
            // In case the uploaded file path is to be stored in the database 
            $filepath = $location . "/" . $filename;
            // Reading file
            $file = fopen($filepath, "r");
            // Read through the file and store the contents as an array
            $importData_arr = array();
            $i = 0;
            //Read the contents of the uploaded file 
            while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                $num = count($filedata);
                // Skip first row (Remove below comment if you want to skip the first row)
                if ($i == 0) {
                    $i++;
                    continue;
                }
                for ($c = 0; $c < $num; $c++) {
                    $importData_arr[$i][] = $filedata[$c];
                }
                $i++;
            }
            fclose($file); //Close after reading
            $j = 0;
            foreach ($importData_arr as $importData) {
                $product = new Products();
                $product->user_id = $user_id;
                $product->category_id = $importData[0];
                $product->product_name = $importData[1];
                $product->sku = $importData[2];
                $product->qty = ($importData[3] == "") ? 0 : $importData[3];
                $product->price = str_replace(',', '', $importData[4]);
                $product->discount_percentage = ($importData[5] == "") ? 0 : $importData[5];
                $product->weight = $importData[6];
                $product->brand = $importData[7];
                $product->size = ($importData[8] == "null") ? NULL : $importData[8];
                $product->status = $importData[9];
                $product->contact = $importData[10];
                $product->colors = ($importData[11] == "null") ? NULL : $importData[11];
                $product->bike = $importData[12];
                $product->van = $importData[13];
                $product->feature_img = $importData[18];
                $product->height = $importData[14];
                $product->width = $importData[15];
                $product->length = $importData[16];
                $product->save();

                $product_images = new productImages();
                $product_images->product_id = (int)$product->id;
                $product_images->product_image = $importData[18];
                $product_images->save();

                $j++;
            }
        }
        flash('Your Bulk Products Have Been Imported Successfully!');
        // if ($request->hasFile('file')) {
        //     $import_data = json_decode($this->csvToJson($request->file('file')), true);
        //     foreach ($import_data as $p) {
        //         if (isset($p['images'])) {
        //             $images = explode(',', $p['images']);
        //             unset($p['images']);
        //         }
        //         if (is_array($p))
        //             $ppt = array_keys($p);
        //         $product = new Products();
        //         print_r($product);
        //         exit;
        //         foreach ($ppt as $t) {
        //             if ($t == 'colors') {
        //                 $colors = explode(',', $p[$t]);
        //                 $product->$t = json_encode(array_fill_keys($colors, true));
        //             } elseif (($t == 'bike' && $p[$t] == null) || ($t == 'van' && $p[$t] == null)) {
        //                 $product->$t = 0;
        //             } else {
        //                 $product->$t = $p[$t];
        //             }
        //         }

        //         $product->user_id = $user_id;
        //         $product->save();
        //         $p_id = $product->id;
        //         if (isset($images)) {
        //             foreach ($images as $image) {
        //                 $product_images = new productImages();
        //                 $product_images->product_id = (int)$p_id;
        //                 $product_images->product_image = $image;
        //                 $product_images->save();
        //             }
        //         }
        //     }

        //     flash('Importing Complete');
        // }
        return redirect()->back();
    }
    /**
     * Change's order status to "ready"
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function change_order_status($order_id)
    {
        Orders::where('id', '=', $order_id)->update(['order_status' => 'ready', 'is_viewed' => 1]);
        $order = Orders::find($order_id);
        $user = $order->user;
        if ($order->type == 'self-pickup') {
            Mail::to($user->email)
                ->send(new OrderIsReadyMail($order));
        }
        return Redirect::back();
    }
    /**
     * Change's order status to "delivered"
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function mark_as_delivered($order_id)
    {
        Orders::where('id', '=', $order_id)->update(['order_status' => 'delivered']);
        flash('This Order Has Been Marked As Delivered')->success();
        return Redirect::back();
    }
    /**
     * It change's the order_status & delivery_status to "complete"
     * Only if the driver is failed to enter the correct verification code
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function mark_as_completed($order_id)
    {
        $verification_codes = VerificationCodes::query()->select('code->driver_failed_to_enter_code as driver_failed_to_enter_code')
            ->where('order_id', '=', $order_id)
            ->get();
        if (json_decode($verification_codes)[0]->driver_failed_to_enter_code == "Yes") {
            Orders::where('id', '=', $order_id)->update(['order_status' => 'complete', 'delivery_status' => 'complete']);
            flash('This Order Has Been Marked As Completed')->success();
        } elseif (json_decode($verification_codes)[0]->driver_failed_to_enter_code == "No") {
            flash('This Order Is Already Marked As Completed')->success();
        } elseif (json_decode($verification_codes)[0]->driver_failed_to_enter_code == "NULL") {
            flash('This Order Cant Be Marked As Completed Because The Verification Code Has Not Been Provided By The Driver Yet')->success();
        }
        return Redirect::back();
    }
    /**
     * Return's admin home view
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
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
    /**
     * Return's admin settings view
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
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
    /**
     * Return's customer details view
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
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
            // $loc = User::where('id', '=', $user_id)
            // ->update(['business_location->lat' => 64.77]);
            // dd($loc);
            $orders = $return_arr;
            return view('admin.customer_details', compact('orders', 'orders_p', 'user'));
        } else {
            abort(401);
        }
    }
    /**
     * Return's admin categories view
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function all_cat()
    {
        $categories = Categories::paginate();
        return view('admin.categories', compact('categories'));
    }
    /**
     * Insert's a new category
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
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
            Storage::disk('spaces')->put($filename, File::get($file));
            if (Storage::disk('spaces')->exists($filename)) {  //check file exists in directory or not
                info("file is store successfully : " . $filename);
                // $filename = "/user_imgs/" . $filename;
            } else {
                info("file is not found :- " . $filename);
            }
            $category->category_image = $filename;
        }
        $category->save();
        flash('Added')->success();
        return Redirect::back();
    }
    /**
     * Update's a specific category
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
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
            Storage::disk('spaces')->put($filename, File::get($file));
            if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
                info("file is store successfully : " . $filename);
                // $filename = "/user_imgs/" . $filename;
            } else {
                info("file is not found :- " . $filename);
            }
            $category->category_image = $filename;
        }
        $category->save();
        flash('Updated')->success();
        return Redirect::back();
    }
    /**
     * Delete's a specific category
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function delete_cat(Request $request)
    {
        DB::table('categories')->where('id', '=', $request->id)->delete();
        flash('Category Deleted Successfully')->success();
        return Redirect::back();
    }

    public function update_pages(Request $request)
    {
        if (Auth::user()->hasRole('superadmin')) {
            $terms_page = Pages::query()->where('page_type', '=', 'terms')->update(['page_content' => $request->tos]);
            $help_page = Pages::query()->where('page_type', '=', 'help')->update(['page_content' => $request->help]);
            $faq_page = Pages::query()->where('page_type', '=', 'faq')->update(['page_content' => $request->faq]);
            return Redirect::back();
        } else {
            abort(404);
        }
    }
    /**
     * Render stores listing view for admin
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
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
    /**
     * Render customers listing view for admin
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
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
    /**
     * Render drivers listing view for admin
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
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
            $orders = $return_arr;
            return view('admin.orders', compact('orders', 'orders_p'));
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
        if (Auth::user()->hasRole('seller')) {
            //$pending_orders = Orders::query()->where('order_status','=','ready')->where('seller_id','=',Auth::id())->count();
            //$total_products = Orders::query()->where('payment_status','!=','hidden')->where('seller_id','=',Auth::id())->count();
            $total_orders = Orders::query()->where('seller_id', '=', Auth::id())->count();
            $user_settings = User::select('settings')->where('id', '=', Auth::id())->get();
            //$total_sales = Orders::query()->where('payment_status','=','paid')->where('seller_id','=',Auth::id())->sum('order_total');
            //return $this->inventory();
            $response = array('total_orders' => $total_orders, 'user_settings' => $user_settings);
            return response()->json($response);
        }
    }

    public function complete_orders()
    {
        $orders = Orders::with(['user', 'delivery_boy'])
            ->has('user')
            ->has('delivery_boy')
            ->where('type', 'delivery')
            ->where('delivery_status', '=', 'pending_approval')
            //            ->where('order_status', 'delivered')
            ->get();
        return view('admin.complete-orders', compact('orders'));
    }

    /**
     * @throws \Twilio\Exceptions\TwilioException
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function mark_complete_order($order_id)
    {
        $order = Orders::with(['user', 'delivery_boy', 'store'])
            ->where('id', $order_id)->first();
        $order->delivery_status = 'complete';
        $order->save();
        // (new OrdersController())->calculateDriverFair($order, $order->store);
        flash('Order is successfully completed')->success();
        $message = "Thanks for your order " . $order->user->name . ".
            Your order from " . $order->store->name . " has successfully been delivered.
            If you have experienced any issues with your order, please contact us via email at:
            admin@teekit.co.uk";
        $sms = new TwilioSmsService();
        $sms->sendSms($order->user->phone, $message);
        Mail::to([$order->user->email])
            ->send(new OrderIsCompletedMail('user'));
        Mail::to([$order->delivery_boy->email])
            ->send(new OrderIsCompletedMail('driver'));
        return \redirect()->route('complete.order');
    }

    /**
     * @throws Stripe\Exception\ApiErrorException
     * @throws \Twilio\Exceptions\TwilioException
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function cancel_order($order_id)
    {
        $order = Orders::findOrFail($order_id);
        $order->load('user');
        $order->load('store');
        Stripe\Stripe::setApiKey(config('app.STRIPE_SECRET'));
        Stripe\Refund::create(['charge' => $order->transaction_id]);
        $order->order_status = 'canceled';
        $order->save();
        $order->update(['order_status' => 'ready']);
        $message = "Hello " . $order->user->name . " .
            Your order from " . $order->store->name . " was unsuccessful.
            Unfortunately " . $order->store->name . " were unable to complete your order. You have not been
            charged.
            If you need any assistance, please contact us via email at:
            admin@teekit.co.uk";
        $sms = new TwilioSmsService();
        $sms->sendSms($order->user->phone, $message);
        Mail::to([$order->user->email])
            ->send(new OrderIsCanceledMail($order));
        flash('Order is successfully canceled')->success();
        return back();
    }
}
