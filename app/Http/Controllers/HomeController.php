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
use App\Drivers;
use App\productImages;
use App\Products;
use App\Qty;
use App\Services\TwilioSmsService;
use App\User;
use App\VerificationCodes;
use App\WithdrawalRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Stripe;
use Throwable;

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
        if (Gate::allows('child_seller')) {
            $child_store = User::where('id', Auth::id())->first();
            $user = User::query()->where('id', '=', Auth::id())->get();
            $pending_orders = Orders::query()->where('order_status', '=', 'pending')->where('seller_id', '=', Auth::id())->count();
            $total_orders = Orders::query()->where('payment_status', '!=', 'hidden')->where('seller_id', '=', Auth::id())->count();
            $total_products = Products::query()->where('user_id', '=', Auth::id())->count();
            $total_sales = Orders::query()->where('payment_status', '=', 'paid')->where('seller_id', '=', Auth::id())->sum('order_total');
            $all_orders = Orders::where('seller_id', Auth::id())
                ->whereNotNull('order_status')
                ->orderby(\DB::raw('case when is_viewed = 0 then 0 when order_status = "pending" then 1 when order_status = "ready" then 2 when order_status = "assigned" then 3
                 when order_status = "onTheWay" then 4 when order_status = "delivered" then 5 end'))
                ->paginate(5);
            return view('shopkeeper.child_dashboard', compact('user', 'pending_orders', 'total_products', 'total_orders', 'total_sales', 'all_orders'));
        }
        if (Gate::allows('seller')) {
            $user = User::query()->where('id', '=', Auth::id())->get();
            $pending_orders = Orders::query()->where('order_status', '=', 'pending')->where('seller_id', '=', Auth::id())->count();
            $total_orders = Orders::query()->where('payment_status', '!=', 'hidden')->where('seller_id', '=', Auth::id())->count();
            $total_products = Products::query()->where('user_id', '=', Auth::id())->count();
            $total_sales = Orders::query()->where('payment_status', '=', 'paid')->where('seller_id', '=', Auth::id())->sum('order_total');
            $all_orders = Orders::where('seller_id', \auth()->id())
                ->whereNotNull('order_status')
                ->orderby(\DB::raw('case when is_viewed = 0 then 0 when order_status = "pending" then 1 when order_status = "ready" then 2 when order_status = "assigned" then 3
                 when order_status = "onTheWay" then 4 when order_status = "delivered" then 5 end'))
                ->paginate(5);
            return view('shopkeeper.dashboard', compact('user', 'pending_orders', 'total_products', 'total_orders', 'total_sales', 'all_orders'));
        } else {
            return $this->adminHome();
        }
    }
    /**
     * It will show the inventory
     * @version 1.1.0
     */
    // public function inventory(Request $request)
    // {
    //     if (Gate::allows('seller') || Gate::allows('child_seller')) {
    //         if (Gate::allows('child_seller')) {
    //             $child_seller_id = Auth::id();
    //             $qty = Qty::where('users_id', $child_seller_id)->first();
    //             $child_seller = User::where('id', $child_seller_id)->first();
    //             $parent_seller_id = $child_seller->parent_store_id;
    //             $featured = Products::query()->where('user_id', $parent_seller_id)->where('featured', '=', 1)->orderBy('id', 'DESC')->get();
    //             if (!empty($qty)) {
    //                 $inventory = Products::where('user_id', $parent_seller_id)
    //                     ->with([
    //                         'quantities' => function ($q) use ($child_seller_id) {
    //                             $q->where('users_id', $child_seller_id);
    //                         }
    //                     ]);
    //             } else {
    //                 $inventory = Products::with('quantity')->where('user_id', $parent_seller_id);
    //             }
    //         }
    //         //if not child seller then this condition will run for parent store
    //         else {
    //             $parent_seller_id = Auth::id();
    //             $inventory = Products::query()->where('user_id', '=', $parent_seller_id)->orderBy('id', 'DESC');
    //             $featured = Products::query()->where('user_id', '=', $parent_seller_id)->where('featured', '=', 1)->orderBy('id', 'DESC')->get();
    //         }
    //         //searching product by name and category
    //         if ($request->search) $inventory = $inventory->where('product_name', 'LIKE', "%{$request->search}%");
    //         if ($request->category) $inventory = $inventory->where('category_id', '=', $request->category);
    //         $categories = Categories::all();
    //         Gate::allows('child_seller') ? $inventory = $inventory->paginate(20) : $inventory = $inventory->paginate(9);
    //         $inventory_p = $inventory;
    //         $inventories = $inventory;
    //         $featured_products = [];
    //         if (Gate::allows('seller')) {
    //             $featured_products = [];
    //             $inventories = [];
    //             foreach ($inventory as $in) {
    //                 $inventories[] = Products::getProductInfo($in->id);
    //             }
    //         }
    //         foreach ($featured as $in) {
    //             $featured_products[] = Products::getProductInfo($in->id);
    //         }
    //         return view('shopkeeper.inventory.list', compact('inventories', 'featured_products', 'inventory_p', 'categories'));
    //     } else {
    //         abort(404);
    //     }
    // }
    /**
     * It will redirect us to
     * edit inventory page
     * @version 1.0.0
     */
    public function inventoryEdit($product_id)
    {
        if (Gate::allows('seller')) {
            $invent = Products::query()->where('user_id', '=', Auth::id())->where('id', '=', $product_id);
            $store = Products::where('id', $product_id)->first();
            $store_id = $store->user_id;
            if (empty($invent)) {
                abort(404);
            }
            $categories = Categories::all();
            $inventory = Products::getProductInfoWithQty($product_id, $store_id);
            return view('shopkeeper.inventory.edit', compact('inventory', 'categories'));
        } else {
            abort(404);
        }
    }
    /**
     * It will redirect us to add
     * inventory page
     * @version 1.0.0
     */
    public function inventoryAdd(Request $request)
    {
        if (Gate::allows('seller')) {
            $categories = Categories::all();
            $inventory = new Products();
            return view('shopkeeper.inventory.add', compact('inventory', 'categories'));
        } else {
            abort(404);
        }
    }
    /**
     * It will redirect us to add
     * inventory in bilk qty page
     * @version 1.0.0
     */
    public function inventoryAddBulk(Request $request)
    {
        if (Gate::allows('seller')) {
            return view('shopkeeper.inventory.add_bulk');
        } else {
            abort(404);
        }
    }
    /**
     * It will delete the product image
     * @version 1.0.0
     */
    public function deleteImg($image_id)
    {
        if (Gate::allows('seller')) {
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
    public function inventoryDisable($product_id)
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
    public function inventoryEnable($product_id)
    {
        $product = Products::find($product_id);
        $product->status = 1;
        $product->save();
        flash('Product Enabled Successfully')->success();
        return Redirect::back();
    }
    /**
     * Enable's all products of logged-in user
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function inventoryEnableAll(Request $request)
    {
        DB::table('products')
            ->where('user_id', Auth::id())
            ->update(['status' => 1]);
        flash('All Products Enabled Successfully')->success();
        return Redirect::back();
    }
    /**
     * Disable's all products of logged-in user
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function inventoryDisableAll(Request $request)
    {
        DB::table('products')
            ->where('user_id', Auth::id())
            ->update(['status' => 0]);
        flash('All Products Disabled Successfully')->success();
        return Redirect::back();
    }
    /**
     * Feature the given product
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function markAsFeatured(Request $request)
    {
        if (Gate::allows('seller')) {
            $count = DB::table('products')
                ->select()
                ->where('user_id', Auth::id())
                ->where('featured', 1)
                ->count();
            if ($count >= 6) {
                flash('You Can Mark Maximum 6 Products As Featured')->success();
            } else {
                DB::table('products')
                    ->where('id', $request->product_id)
                    ->update(['featured' => 1]);
                flash('Marked As Featured, Successfully')->success();
            }
            return Redirect::back();
        } else {
            abort(404);
        }
    }
    /**
     * Remove the given product from featured list
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function removeFromFeatured(Request $request)
    {
        if (Gate::allows('seller')) {
            DB::table('products')
                ->where('id', $request->product_id)
                ->update(['featured' => 0]);
            flash('Removed From Featured, Successfully')->success();
        }
        return Redirect::back();
    }
    /**
     * Inserts a single store product
     * @author Mirza Abdullah Izhar
     * @version 1.2.0
     */
    public function inventoryAddDB(Request $request)
    {
        if (Gate::allows('seller')) {
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
            unset($data['qty']);
            $user_id = Auth::id();
            $data['user_id'] = $user_id;
            $product = new Products();
            if (!empty($product)) {
                $filename = $product->feature_img;
                if ($request->hasFile('feature_img')) {
                    $file = $request->file('feature_img');
                    $filename = uniqid($user_id . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
                    Storage::disk('spaces')->put($filename, File::get($file));
                    if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
                        info("file is stored successfully : " . $filename);
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
                $product_id = $product->id;
                $product_quantity = $request->qty;
                $this->addProductQty($product_id, $user_id, $product_quantity);
                if ($request->hasFile('gallery')) {
                    $images = $request->file('gallery');
                    foreach ($images as $image) {
                        $file = $image;
                        $filename = uniqid($user_id . "_" . $product->id . "_") . "." . $file->getClientOriginalExtension(); //create unique file name...
                        Storage::disk('spaces')->put($filename, File::get($file));
                        if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
                            info("file is stored successfully : " . $filename);
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
    public function inventoryUpdate(Request $request, $product_id)
    {
        if (Gate::allows('seller')) {
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
            Qty::where('products_id', $product_id)
                ->where('users_id', Auth::id())
                ->update([
                    'qty' => $data['qty'],
                ]);
            unset($data['qty']);
            $product = Products::find($product_id);
            if (!empty($product)) {
                $filename = $product->feature_img;
                if ($request->hasFile('feature_img')) {
                    $file = $request->file('feature_img');
                    $filename = uniqid($product->id . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
                    Storage::disk('spaces')->put($filename, File::get($file));
                    if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
                        info("file is stored successfully : " . $filename);
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
                            info("file is stored successfully : " . $filename);
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
    public function userImgUpdate(Request $request)
    {
        $user = User::find(\auth()->id());
        $filename = \auth()->user()->name;
        if ($request->hasFile('user_img')) {
            $file = $request->file('user_img');
            $filename = uniqid($user->id . '_' . $user->name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
            Storage::disk('spaces')->put($filename, File::get($file));
            if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
                info("file is stored successfully : " . $filename);
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
    public function changeSettings(Request $request)
    {
        User::where('id', '=', Auth::id())->update(['settings->' . $request->setting_name => $request->value]);
        return \redirect()->route('home');
    }
    /**
     * Display's payment view
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function paymentSettings()
    {
        $payment_settings = User::find(Auth::id())->bank_details;
        return view('shopkeeper.settings.payment', compact('payment_settings'));
    }
    /**
     * Display's store general settings
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function generalSettings()
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
    public function timeUpdate(Request $request)
    {
        $time = $request->time;
        foreach ($time as $key => $value) {
            if (!in_array("on", $time[$key]))
                $time[$key] += ["closed" => null];
        }
        $data['time'] = $time;
        $data['submitted'] = "yes";
        $user = User::find(Auth::id());
        $user->business_hours = json_encode($data);
        $user->save();
        sleep(1);
        session()->flash('success', 'Business Hours Updated');
        return redirect()->back();
    }
    /**
     * Update's user location
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function locationUpdate(Request $request)
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
     * Update's user password
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function passwordUpdate(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8'
        ]);
        if ($validate->fails()) {
            flash('Password must be 8 characters long.')->error();
            return Redirect::back();
        }

        $old_password = $request->old_password;
        $new_password = $request->new_password;

        $user = User::find(Auth::id());
        if (Hash::check($old_password, $user->password)) {
            $user->password = Hash::make($new_password);
            $user->save();
            flash('Your password has been updated successfully.')->success();
        } else {
            flash('Your old password is incorrect.')->error();
        }
        return redirect()->back();
    }
    /**
     * Update's payment settings
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function paymentSettingsUpdate(Request $request)
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
    /**
     * It will showorders
     * @version 1.0.0
     */
    public function orders(Request $request)
    {
        //        $inventory = Products::query()->where('user_id','=',Auth::id())->paginate(9);
        //        $inventory_p = $inventory;
        //        $inventories = [];
        //        foreach ($inventory as $in){
        //            $inventories[] = Products::getProductInfo($in->id);
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
                $product = (new ProductsController())->getProductInfo($item->product_id);
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
     * Since our qty has now it's separate migration,
     * this will help us add qty with given details to qty table
     * @version 1.0.0
     */
    public function addProductQty($product_id, $user_id, $product_quantity)
    {
        $quantity = new Qty();
        $quantity->products_id = $product_id;
        $quantity->users_id = $user_id;
        $quantity->qty = $product_quantity;
        $quantity->save();
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
            // $extension = $file->getClientOriginalExtension(); //Get extension of uploaded file
            // $tempPath = $file->getRealPath();
            // $fileSize = $file->getSize(); //Get size of uploaded file in bytes

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
                $product->price = str_replace(',', '', $importData[4]);
                $product->discount_percentage = ($importData[5] == "") ? 0 : $importData[5];
                $product->weight = $importData[6];
                $product->brand = $importData[7];
                $product->size = ($importData[8] == "null") ? NULL : $importData[8];
                $product->status = $importData[9];
                $product->contact = $importData[10];
                $product->colors = ($importData[11] == "null") ? NULL : $importData[11];
                $product->bike = $importData[12];
                $product->car = $importData[13];
                $product->van = $importData[14];
                $product->feature_img = $importData[18];
                $product->height = $importData[15];
                $product->width = $importData[16];
                $product->length = $importData[17];
                $product->save();

                //this function will add qty to it's particular table
                $product_id = (int)$product->id;
                $product_quantity = ($importData[3] == "") ? 0 : $importData[3];
                $this->addProductQty($product_id, $user_id, $product_quantity);
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
    public function changeOrderStatus($order_id)
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
    public function markAsDelivered($order_id)
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
    public function markAsCompleted($order_id)
    {
        $verification_codes = VerificationCodes::query()->select('code->driver_failed_to_enter_code as driver_failed_to_enter_code')
            ->where('order_id', '=', $order_id)
            ->get();
        if (json_decode($verification_codes)[0]->driver_failed_to_enter_code == "Yes" || json_decode($verification_codes)[0]->driver_failed_to_enter_code == "NULL") {
            Orders::where('id', '=', $order_id)->update(['order_status' => 'complete', 'delivery_status' => 'complete']);
            flash('This Order Has Been Marked As Completed')->success();
        } elseif (json_decode($verification_codes)[0]->driver_failed_to_enter_code == "No") {
            flash('This Order Is Already Marked As Completed')->success();
        }
        return Redirect::back();
    }
    /**
     * Return's admin home view
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function adminHome()
    {
        if (Gate::allows('superadmin')) {
            $terms_page = Pages::query()->where('page_type', '=', 'terms')->first();
            $help_page = Pages::query()->where('page_type', '=', 'help')->first();
            $faq_page = Pages::query()->where('page_type', '=', 'faq')->first();
            $slogan = Pages::query()->where('page_type', '=', 'slogan')->first();
            $favicon = Pages::query()->where('page_type', '=', 'favicon')->first();
            $logo = Pages::query()->where('page_type', '=', 'logo')->first();

            $pending_orders = Orders::query()->where('order_status', '=', 'ready')->count();
            $total_products = Products::query()->count();
            $total_orders = Orders::query()->where('payment_status', '!=', 'hidden')->count();
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
    public function aSetting()
    {
        if (Gate::allows('superadmin')) {
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
     * @version 1.1.0
     */
    public function adminCustomerDetails($user_id)
    {
        $return_arr = [];
        if (Gate::allows('superadmin')) {
            $user = User::find($user_id);
            // 2: Parent seller, 5: Child seller
            if ($user->role_id == 2 || $user->role_id == 5) $orders = Orders::query()->where('seller_id', '=', $user_id);
            // For buyer
            if ($user->role_id == 3) $orders = Orders::query()->where('user_id', '=', $user_id);

            $orders = $orders->where('payment_status', '!=', 'hidden')->orderByDesc('id');
            $orders = $orders->paginate(10);
            $orders_p = $orders;
            foreach ($orders as $order) {
                $items = OrderItems::query()->where('order_id', '=', $order->id)->get();
                $item_arr = [];
                foreach ($items as $item) {
                    $product = (new ProductsController())->getProductInfo($item->product_id);
                    $item['product'] = $product;
                    $item_arr[] = $item;
                }
                $order['items'] = $item_arr;
                $return_arr[] = $order;
            }
            $role_id = $user->role_id;
            $orders = $return_arr;
            return view('admin.customer_details', compact('orders', 'orders_p', 'user', 'role_id'));
        } else {
            abort(401);
        }
    }
    /**
     * Return's driver details view
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function adminDriverDetails($driver_id)
    {
        if (Gate::allows('superadmin')) {
            $return_arr = [];
            /**
             * In Teek it delivery boy == driver
             * So when we need delivery boy details
             * We have to goto the drivers table
             */
            $driver = Drivers::find($driver_id);
            $orders = Orders::query()
                ->where('delivery_boy_id', '=', $driver_id)
                ->where('payment_status', '!=', 'hidden')
                ->orderByDesc('id');
            $role_id = 4;
            $orders = $orders->where('payment_status', '!=', 'hidden')->orderByDesc('id');
            $orders = $orders->paginate(10);
            $orders_p = $orders;
            foreach ($orders as $order) {
                $items = OrderItems::query()->where('order_id', '=', $order->id)->get();
                $item_arr = [];
                foreach ($items as $item) {
                    $product = (new ProductsController())->getProductInfo($item->product_id);
                    $item['product'] = $product;
                    $item_arr[] = $item;
                }
                $order['items'] = $item_arr;
                $return_arr[] = $order;
            }
            $orders = $return_arr;
            return view('admin.driver_details', compact('orders', 'orders_p', 'driver', 'role_id'));
        } else {
            abort(401);
        }
    }
    /**
     * Return's admin categories view
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function allCat()
    {
        $categories = Categories::paginate();
        return view('admin.categories', compact('categories'));
    }
    /**
     * Insert's a new category
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function addCat(Request $request)
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
                info("file is stored successfully : " . $filename);
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
    public function updateCat(Request $request, $id)
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
                info("file is stored successfully : " . $filename);
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
     * @version 1.1.0
     */
    public function deleteCat(Request $request)
    {
        if (Gate::allows('superadmin')) {
            DB::table('categories')->where('id', '=', $request->id)->delete();
            flash('Category Deleted Successfully')->success();
        }
        return Redirect::back();
    }

    public function updatePages(Request $request)
    {
        if (Gate::allows('superadmin')) {
            $terms_page = Pages::query()->where('page_type', '=', 'terms')->update(['page_content' => $request->tos]);
            $help_page = Pages::query()->where('page_type', '=', 'help')->update(['page_content' => $request->help]);
            $faq_page = Pages::query()->where('page_type', '=', 'faq')->update(['page_content' => $request->faq]);
            return Redirect::back();
        } else {
            abort(404);
        }
    }
    /**
     * Render parent sellers list view for admin
     * @author Huzaifa Haleem
     * @version 1.1.0
     */
    // public function adminParentSellers(Request $request)
    // {
    //     if (Gate::allows('superadmin')) {
    //         $users = User::query()->where('role_id', 2);
    //         if ($request->search) {
    //             $users = $users->where('business_name', 'LIKE', $request->search);
    //         }
    //         $users = $users->paginate(9);
    //         return view('admin.parent_sellers', compact('users'));
    //     } else {
    //         abort(404);
    //     }
    // }

    /**
     * Render child sellers list view for admin
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    // public function adminChildSellers(Request $request)
    // {
    //     if (Gate::allows('superadmin')) {
    //         $users = User::query()->where('role_id', 5);
    //         if ($request->search) {
    //             $users = $users->where('business_name', 'LIKE', $request->search);
    //         }
    //         $users = $users->paginate(9);
    //         return view('admin.child_sellers', compact('users'));
    //     } else {
    //         abort(404);
    //     }
    // }
    /**
     * Delete selected users
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function adminUsersDel(Request $request)
    {
        if (Gate::allows('superadmin')) {
            // dd($request->users);
            for ($i = 0; $i < count($request->users); $i++) {
                User::findOrfail($request->users[$i])->delete();
                // Del Products & Orders

                // DB::table('users')->where('id', '=', $request->users[$i])->delete();
                /* Obselete Code */
                // DB::table('role_user')->where('user_id', '=', $request->users[$i])->delete();
            }
            return response("Users Deleted Successfully");
        }
    }
    /**
     * Delete selected drivers
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function adminDriversDel(Request $request)
    {
        if (Gate::allows('superadmin')) {
            for ($i = 0; $i < count($request->drivers); $i++) {
                DB::table('drivers')->where('id', '=', $request->drivers[$i])->delete();
                DB::table('driver_documents')->where('driver_id', '=', $request->drivers[$i])->delete();
            }
            return response("Drivers Deleted Successfully");
        }
    }
    /**
     * Render customers listing view for admin
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function adminCustomers(Request $request)
    {
        if (Gate::allows('superadmin')) {
            $users = User::where('role_id', 3)->orderByDesc('created_at');
            if ($request->search) {
                $users = $users->where('name', 'LIKE', $request->search);
            }
            $users = $users->paginate(9);
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
    public function adminDrivers(Request $request)
    {
        if (Gate::allows('superadmin')) {
            $drivers = Drivers::query();
            if ($request->search) $drivers = $drivers->where('f_name', 'LIKE', $request->search);
            $drivers = $drivers->paginate(9);
            return view('admin.drivers', compact('drivers'));
        } else {
            abort(404);
        }
    }
    /**
     * Render orders listing view for admin
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function adminOrders(Request $request)
    {
        if (Gate::allows('superadmin')) {
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
                    $product = (new ProductsController())->getProductInfo($item->product_id);
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
    /**
     * Render verified orders listing view for admin
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function adminOrdersVerified(Request $request)
    {
        if (Gate::allows('superadmin')) {
            $return_arr = [];
            $verified_orders = VerificationCodes::query()
                ->where('code->driver_failed_to_enter_code', '=', 'No')
                ->orderByDesc('id');
            // if ($request->search) {
            //     $orders = $orders->where('id', '=', $request->search);
            // }
            // if ($request->user_id) {
            //     $orders = $orders->where('user_id', '=', $request->user_id);
            // }
            // if ($request->store_id) {
            //     $orders = $orders->where('seller_id', '=', $request->store_id);
            // }
            $verified_orders = $verified_orders->paginate(10);
            $orders_p = $verified_orders;
            foreach ($verified_orders as $order) {
                $order_details = Orders::query()->where('id', '=', $order->order_id)->first();
                $items = OrderItems::query()->where('order_id', '=', $order->order_id)->get();
                $item_arr = [];
                foreach ($items as $item) {
                    $product = (new ProductsController())->getProductInfo($item->product_id);
                    $item['product'] = $product;
                    $item_arr[] = $item;
                }
                $order['order_details'] = $order_details;
                $order['items'] = $item_arr;
                $return_arr[] = $order;
            }
            $orders = $return_arr;
            return view('admin.verified_orders', compact('orders', 'orders_p'));
        } else {
            abort(404);
        }
    }
    /**
     * Render unverified orders listing view for admin
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function adminOrdersUnverified(Request $request)
    {
        if (Gate::allows('superadmin')) {
            $return_arr = [];
            $verified_orders = VerificationCodes::query()
                ->where('code->driver_failed_to_enter_code', '=', 'Yes')
                ->orderByDesc('id');
            // if ($request->search) {
            //     $orders = $orders->where('id', '=', $request->search);
            // }
            // if ($request->user_id) {
            //     $orders = $orders->where('user_id', '=', $request->user_id);
            // }
            // if ($request->store_id) {
            //     $orders = $orders->where('seller_id', '=', $request->store_id);
            // }
            $verified_orders = $verified_orders->paginate(10);
            $orders_p = $verified_orders;
            foreach ($verified_orders as $order) {
                $order_details = Orders::query()->where('id', '=', $order->order_id)->first();
                $items = OrderItems::query()->where('order_id', '=', $order->order_id)->get();
                $item_arr = [];
                foreach ($items as $item) {
                    $product = (new ProductsController())->getProductInfo($item->product_id);
                    $item['product'] = $product;
                    $item_arr[] = $item;
                }
                $order['order_details'] = $order_details;
                $order['items'] = $item_arr;
                $return_arr[] = $order;
            }
            $orders = $return_arr;
            return view('admin.unverified_orders', compact('orders', 'orders_p'));
        } else {
            abort(404);
        }
    }
    /**
     * Delete selected orders
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function adminOrdersDel(Request $request)
    {
        if (Gate::allows('superadmin')) {
            for ($i = 0; $i < count($request->orders); $i++) {
                DB::table('orders')->where('id', '=', $request->orders[$i])->delete();
                DB::table('order_items')->where('order_id', '=', $request->orders[$i])->delete();
                DB::table('verification_codes')->where('order_id', '=', $request->orders[$i])->delete();
            }
            return response("Orders Deleted Successfully");
        }
    }
    /**
     * It will show withdrawls to seller/admin
     * based on their auth id
     * @version 1.0.0
     */
    public function withdrawals()
    {
        if (Gate::allows('seller') || Gate::allows('child_seller')) {
            $user_id = Auth::id();
            $return_data = WithdrawalRequests::query()->where('user_id', '=', $user_id)->get();
            $transactions = $return_data;
            return view('shopkeeper.withdrawal', compact('transactions'));
        }
        if (Gate::allows('superadmin')) {
            $user_id = Auth::id();
            $return_data = WithdrawalRequests::has('user.seller')->get();
            $transactions = $return_data;
            return view('admin.withdrawal', compact('transactions'));
        }
    }
    /**
     * It will show driver withdrawls
     * @version 1.0.0
     */
    public function withdrawalDrivers()
    {
        if (Gate::allows('seller')) {
            $user_id = Auth::id();
            $return_data = WithdrawalRequests::query()->where('user_id', '=', $user_id)->get();
            $transactions = $return_data;
            return view('shopkeeper.withdrawal', compact('transactions'));
        }
        if (Gate::allows('superadmin')) {

            // $transactions = WithdrawalRequests::has('user.driver')->get();
            return view('admin.withdrawal-drivers');
        }
    }
    /**
     * It will show seller withdrawls requests
     * @version 1.0.0
     */
    public function withdrawalsRequest(Request $request)
    {
        if (Gate::allows('seller')) {
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
        if (Gate::allows('superadmin')) {
            $with = WithdrawalRequests::find($request->id);
            $with->status = $request->status;
            $with->transaction_id = $request->t_id;
            $with->save();
            flash('Updated')->success();
            return Redirect::back();
        }
    }
    /**
     * It will change the store status
     * @version 1.0.0
     */
    public function changeUserStatus($user_id, $status)
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

    public function adminQueries()
    {
        if (Gate::allows('superadmin')) {
            return view('admin.queries');
        } else {
            abort(404);
        }
    }
    /**
     * It will show the order count
     * @version 1.0.0
     */
    public function myOrderCount()
    {
        if (Gate::allows('superadmin')) {
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
    /**
     * It will show complete orders
     * based on the given criteria
     * @version 1.0.0
     */
    public function completeOrders()
    {
        $orders = DB::table('orders')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->LeftJoin('drivers', 'orders.delivery_boy_id', '=', 'drivers.id')
            ->where('delivery_status', '=', 'complete')
            ->where('order_status', '=', 'complete')
            ->select('drivers.f_name', 'drivers.l_name', 'orders.id', 'orders.total_items', 'orders.phone_number', 'orders.house_no', 'orders.address',  'orders.type', 'users.name')
            ->paginate(10);
        return view('admin.complete-orders', compact('orders'));
    }
    /**
     * @throws \Twilio\Exceptions\TwilioException
     * @throws \Twilio\Exceptions\ConfigurationException
     * It will mark the order as complete
     * @version 1.0.0
     */
    public function markCompleteOrder($order_id)
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
     * It will change the order status to canceled
     */
    public function cancelOrder($order_id)
    {
        $order = Orders::findOrFail($order_id);
        $order->load('user');
        $order->load('store');
        // dd($order->transaction_id);
        Stripe\Stripe::setApiKey(config('app.STRIPE_SECRET'));
        Stripe\Refund::create(['charge' => $order->transaction_id]);
        $order->order_status = 'cancelled';
        $order->save();
        $message = "Hello " . $order->user->name . " .
            Your order from " . $order->store->name . " was unsuccessful.
            Unfortunately " . $order->store->name . " is unable to complete your order. But don't worry 
            you have not been charged.
            If you need any kinda of assistance, please contact us via email at:
            admin@teekit.co.uk";
        $sms = new TwilioSmsService();
        $sms->sendSms($order->user->phone, $message);
        Mail::to([$order->user->email])
            ->send(new OrderIsCanceledMail($order));
        flash('Order is successfully cancelled')->success();
        return back();
    }
    /**
     * It will remove a single product from the given order
     * @version 1.0.0
     */
    public function removeProductFromOrder($order_id, $item_id, $product_price, $product_qty)
    {
        try {
            $order = Orders::find($order_id);
            $order->order_total -= $product_price;
            $order->total_items -= $product_qty;
            $order->save();
            // Now remove the product from order items table
            $removed = OrderItems::where('id', '=', $item_id)->delete();
            if ($removed) {
                flash('Product Has Been Removed Successfully')->success();
                return Redirect::back();
            }
        } catch (Throwable $error) {
            report($error);
            flash('Error In Removing The Product')->error();
            return Redirect::back();
        }
    }
    /**
     * it will update the store info via popup modal
     * @author Mirza Abdullah Izhar
     * @version 1.3.0
     */
    public function updateStoreInfo(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string',
            'business_name' => 'required|string',
            'phone' => 'required|max:13',
            'business_phone' => 'required|max:13',
        ]);
        if ($validatedData->fails()) {
            return response()->json([
                'errors' => $validatedData->errors()
            ], 200);
            exit;
        }
        $phone = substr($request->phone, 0, 3);
        $business_phone = substr($request->business_phone, 0, 3);
        $store_info = User::find($request->id);
        if ($request->hasFile('store_image')) {
            $file = $request->file('store_image');
            $filename = uniqid($store_info->id . "_" . $store_info->name . "_") . "." . $file->getClientOriginalExtension(); //create unique file name...
            Storage::disk('spaces')->put($filename, File::get($file));
            if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
                info("file is stored successfully : " . $filename);
            } else {
                info("file is not found :- " . $filename);
            }
        }
        $filename = $store_info->user_img;
        if ($phone == '+44') {
            $store_info->phone = $request->phone;
        } else {
            $store_info->phone = '+44' . $request->phone;
        }
        if ($business_phone == '+44') {
            $store_info->business_phone = $request->business_phone;
        } else {
            $store_info->business_phone = '+44' . $request->business_phone;
        }
        $store_info->name = $request->name;
        $store_info->business_name = $request->business_name;
        $store_info->user_img = $filename;
        $store_info->save();
        if ($store_info) {
            echo 'Data Saved';
        }
    }
    /**
     * it will update the user info via popup modal
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function userInfoUpdate(Request $request)
    {
        $is_valid = Validator::make($request->all(), [
            'name' => 'required|string',
            'business_name' => 'required|string',
            'phone' => 'required|string|min:13|max:13',
            'business_phone' => 'required|string|min:13|max:13',
        ]);
        if ($is_valid->fails()) {
            return response()->json([
                'errors' => $is_valid->errors()
            ], 200);
            exit;
        }
        $store_name = User::find($request->id);
        if ($request->all()) {
            echo "Data Sent";
        }
        $html = '<html>
        Hi, Team Teek IT.<br><br>
        '  .  $store_name->business_name   .  ' has demanded to update their business information as following:-<br><br>
       <strong> Name:</strong> '  .  $request->name   .  '<br>
       <strong>Business Name:</strong> '  .  $request->business_name   .  '<br>
       <strong>Phone:</strong> '  .  $request->phone  .  '<br>
       <strong>Business Phone:</strong> '  .  $request->business_phone  .  '<br>
       <br><br>
       Please verify this information & take your desision about modifying their business information.
       <br><br>
       From,
       <br>
       Teek it
       </html>';
        $subject = env('APP_NAME') . ': User Info Update';
        Mail::to(config('constants.ADMIN_EMAIL'))
            ->send(new StoreRegisterMail($html, $subject));
    }
    /**
     * it will update the unverified orders to verified
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function clickToVerify($order_id)
    {
        VerificationCodes::where('order_id', $order_id)
            ->update(['code->driver_failed_to_enter_code' => 'No']);
        flash('Order Verified Successfully')->success();
        return Redirect::back();
    }
}
