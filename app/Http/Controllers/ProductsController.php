<?php

namespace App\Http\Controllers;
use App\Categories;
use App\productImages;
use App\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\User;
use App\Qty;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Stripe\Product;
use Throwable;

class ProductsController extends Controller
{
    /**
     * one time use method to drop
     * qty column from products table
     * @version 1.0.0
     */
    public function dropProductsTableQtyColumn()
    {
        try {
            DB::statement(
                'ALTER TABLE products DROP qty'
            );
            return response()->json([
                'status' => true,
                'message' => 'Column dropped successfully'
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
     * Since our qty has now it's separate migration,
     * this will help us update qty with given details to qty table
     * @version 1.0.0
     */
    public function updateProductQty($product_id, $user_id, $product_quantity)
    {
        Qty::where('products_id', $product_id)
            ->where('users_id', $user_id)->update([
                'qty' => $product_quantity
            ]);
    }
    /**
     *It will insert a single product
     *and insert it's given qty to qty table
     * @version 1.0.0
     */
    public function add(Request $request)
    {
        $validate = Products::validator($request);
        if ($validate->fails()) {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.VALIDATION_ERROR')
            ], 400);
        }
        $user_id = Auth::id();
        $product = new Products();
        $product->category_id = $request->category_id;
        $product->product_name = $request->product_name;
        $product->product_description = $request->product_description;
        $product->color = $request->color;
        $product->size = $request->size;
        $product->lat = $request->lat;
        $product->lon = $request->lon;
        $product->price = $request->price;
        // $product->qty = $request->qty;
        $product->user_id = $user_id;
        $product->save();
        //this function will add qty to it's particular table
        $product_id = $product->id;
        $product_quantity = $request->qty;
        $this->addProductQty($product_id, $user_id, $product_quantity);
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            foreach ($images as $image) {
                $file = $image;
                $filename = uniqid($user_id . "_" . $product->id . "_") . "." . $file->getClientOriginalExtension(); //create unique file name..
                Storage::disk('user_public')->put($filename, File::get($file));
                if (Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
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
        $product =  $this->getProductInfo($product->id);
        return response()->json([
            'data' => $product,
            'status' => true,
            'message' => config('constants.DATA_INSERTION_SUCCESS')
        ], 200);
    }
    /**
     * Upload's bulk products
     * This function belongs to import products API
     * This is the replice of import products WEB URL
     * @version 1.0.0
     */
    public function importProductsAPI(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'file' => 'required|file',
                'store_id' => 'required|integer'
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' =>  $validatedData->errors()
                ], 422);
            }
            $user_id = $request->store_id;
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
                // $product->qty = ($importData[3] == "") ? 0 : $importData[3];
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
            return response()->json([
                'data' => [],
                'status' => false,
                'message' =>  config('constants.DATA_INSERTION_SUCCESS')
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
    /**
     *It will update a single product
     *and update the given qty in qty table
     * @version 1.0.0
     */
    public function update(Request $request, $id)
    {
        $validate = Products::updateValidator($request);
        if ($validate->fails()) {
            return response()->json([
                'data' => $validate->messages(),
                'status' => true,
                'message' => config('constants.VALIDATION_ERROR')
            ], 400);
        }
        $user_id = Auth::id();
        $product = Products::find($id);
        if (empty($product)) {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 400);
        }
        $product->category_id = $request->category_id;
        $product->product_name = $request->product_name;
        $product->product_description = $request->product_description;
        $product->color = $request->color;
        $product->size = $request->size;
        $product->lat = $request->lat;
        $product->lon = $request->lon;
        $product->price = $request->price;
        // $product->qty = $request->qty;
        $product->user_id = $user_id;
        if ($request->hasFile('images')) {
            $images = $request->file('images');
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
        $product->save();
        //this function will update qty in it's particular table with given data
        $product_id = $product->id;
        $product_quantity = $request->qty;
        $this->updateProductQty($product_id, $user_id, $product_quantity);
        $product =  $this->getProductInfo($product->id);
        return response()->json([
            'data' => $product,
            'status' => true,
            'message' => config('constants.DATA_UPDATED_SUCCESS')
        ], 200);
    }
    /**
     * This function return product information
     * as well as qty data for the products from qty table
     * @version 1.0.0
     */
    public function getProductInfo($product_id)
    {
        $qty = Products::with('quantity')
            ->where('id', $product_id)
            ->first();
        $quantity = $qty->quantity->qty;
        $product = Products::with('quantity')
            ->select(['*', DB::raw("$quantity as qty")])
            ->find($product_id);
        $product->images = productImages::query()->where('product_id', '=', $product->id)->get();

        $product->category = Categories::find($product->category_id);
        $product->ratting = (new RattingsController())->get_ratting($product_id);
        return $product;
    }
    public function getProductInfoWithQty($product_id, $store_id)
    {
        $qty = Products::with('quantity')
            ->where('user_id', $store_id)
            ->where('id', $product_id)
            ->first();
        $quantity = $qty->quantity->qty;
        $product = Products::with('quantity')
            ->where('user_id', $store_id)
            ->where('id', $product_id)
            ->select(['*', DB::raw("$quantity as qty")])
            ->first();
        $product->images = productImages::query()->where('product_id', '=', $product->id)->get();
        $product->category = Categories::find($product->category_id);
        $product->ratting = (new RattingsController())->get_ratting($product_id);
        return $product;
    }
    /**
     * All products listing
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function all()
    {
        try {
            $products = Products::whereHas('user', function ($query) {
                $query->where('is_active', 1);
            })->where('status', 1)->paginate();
            $pagination = $products->toArray();
            if (!empty($products)) {
                $products_data = [];
                foreach ($products as $product) {
                    $data = $this->getProductInfo($product->id);
                    $data->store = User::find($product->user_id);
                    $products_data[] = $data;
                }
                unset($pagination['data']);
                return response()->json([
                    'data' => $products_data,
                    'status' => true,
                    'message' => '',
                    'pagination' => $pagination,
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     *View products in bulk with array of given ids
     * @version 1.0.0
     */
    public function bulkView(Request $request)
    {
        $ids = explode(',', $request->ids);
        $products = Products::query()->whereIn('id', $ids)->paginate();
        $pagination = $products->toArray();
        if (!empty($products)) {
            $products_data = [];
            foreach ($products as $product) {
                $products_data[] = $this->getProductInfo($product->id);
            }
            unset($pagination['data']);
            return response()->json([
                'data' => $products_data,
                'status' => true,
                'message' => '',
                'pagination' => $pagination,
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
     *It will sort the products by price
     * @version 1.0.0
     */
    public function sortByPrice()
    {
        try {
            $products = Products::query()->paginate()->sortBy('price');
            $pagination = $products->toArray();
            if (!$products->isEmpty()) {
                $products_data = [];
                foreach ($products as $product) {
                    $products_data[] = $this->getProductInfo($product->id);
                }
                unset($pagination['data']);
                return response()->json([
                    'data' => $products_data,
                    'status' => true,
                    'message' => '',
                    'pagination' => $pagination,
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     *It will sort the products by location
     * @version 1.0.0
     */
    public function sortByLocation(Request $request)
    {
        $latitude = $request->get('lat');
        $longitude = $request->get('lon');
        $products = Products::select(DB::raw('*, ( 6367 * acos( cos( radians(' . $latitude . ') ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude . ') ) * sin( radians( lat ) ) ) ) AS distance'))->paginate()->sortBy('distance');
        $pagination = $products->toArray();
        if (!empty($products)) {
            $products_data = [];
            $i = 0;
            foreach ($products as $product) {
                if ($i == 50) {
                    continue;
                }
                $i = $i + 1;
                $t = $this->getProductInfo($product->id);
                $t->distance = $product->distance;
                //$t->distance = round($product->distance);
                $products_data[] = $t;
            }
            unset($pagination['data']);
            return response()->json([
                'data' => $products_data,
                'status' => true,
                'message' => '',
                //'pagination'=>$pagination,
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
     * View product w.r.t ID
     * @author Mirza Abdullah Izhar
     * @version 1.2.0
     */
    public function view(Request $request)
    {
        try {
            $validate = Validator::make($request->route()->parameters(), [
                'product_id' => 'required|integer',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validate->errors()
                ], 422);
            }
            $store = Products::where('id', $request->product_id)->first();
            $store_id = $store->user_id;
            $product = $this->getProductInfoWithQty($request->product_id, $store_id);
            if (!empty($product)) {
                $product->store = User::find($product->user_id);
                unset($product->quantity);
                return response()->json([
                    'data' => $product,
                    'status' => true,
                    'message' => "",
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * It will delete the given product
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function delete($product_id)
    {
        Products::find($product_id)->delete();
        return $this->all();
    }
    /**
     * It will delete the image of the given product
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function deleteImage($image_id, $product_id)
    {
        productImages::find($image_id)->delete();
        return $this->getProductInfo($product_id);
    }
    /**
     * It list the featured products
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function featuredProducts(Request $request)
    {
        try {
            $featured_products = Products::whereHas('user', function ($query) {
                $query->where('is_active', 1);
            })->where('user_id', '=', $request->store_id)
                ->where('featured', '=', 1)
                ->where('status', '=', 1)
                ->orderByDesc('id')
                ->paginate(10);
            $pagination = $featured_products->toArray();
            if (!$featured_products->isEmpty()) {
                $products_data = [];
                foreach ($featured_products as $product) {
                    $data = $this->getProductInfo($product->id);
                    $data->store = User::find($product->user_id);
                    $products_data[] = $data;
                }
                unset($pagination['data']);
                return response()->json([
                    'data' => $products_data,
                    'status' => true,
                    'message' => '',
                    'pagination' => $pagination,
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * It find's the price of the given product
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function get_product_price($product_id)
    {
        $product = Products::find($product_id);
        if ($product->discount_percentage > 0) {
            return $product->discount_percentage * 1.2;
        }
        return $product->price * 1.2;
    }
    /**
     * It find's the volumn of the given product
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function get_product_volumn($product_id)
    {
        $product = DB::table('products')
            ->select(DB::raw('(products.height * products.width * products.length) as volumn'))
            ->where('id', $product_id)
            ->get();
        return $product[0]->volumn;
    }
    /**
     * It find's the weight of the given product
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function get_product_weight($product_id)
    {
        $product = DB::table('products')
            ->select('weight')
            ->where('id', $product_id)
            ->get();
        return $product[0]->weight;
    }
    /**
     * It find's the seller_id of the given product
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function get_product_seller_id($product_id)
    {
        return Products::find($product_id)->user_id;
    }
    /**
     * Subtracts or Add qty
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function updateQty($product_id, $qty, $operation)
    {
        if ($operation == 'subtract') {
            $qty = Qty::where('products_id', '=', $product_id)
                ->decrement('qty', $qty);
        }
    }
    /**
     *It will export products into csv
     * @version 1.0.0
     */
    public function exportProducts()
    {
        $user_id = Auth::id();
        $products = Products::query()->where('user_id', '=', $user_id)->orderBy('id', 'ASC')->get();
        $all_products = [];
        foreach ($products as $product) {
            $pt = json_decode(json_encode($this->getProductInfo($product->id)->toArray()));
            unset($pt->category);
            unset($pt->ratting);
            unset($pt->id);
            unset($pt->user_id);
            unset($pt->created_at);
            unset($pt->updated_at);
            $temp_img = [];
            if (isset($pt->images)) {
                foreach ($pt->images as $img) {
                    $temp_img[] = $img->product_image;
                }
            }
            $pt->images = implode(',', $temp_img);
            $all_products[] = $pt;
        }
        $destinationPath = public_path() . "/upload/csv/";
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        $file = time() . '_export.csv';
        return  $this->jsonToCsv(json_encode($all_products), $destinationPath . $file, true);
    }
    /**
     *helper function for exporting products
     * @version 1.0.0
     */
    function jsonToCsv($json, $csvFilePath = false, $boolOutputFile = false)
    {
        // See if the string contains something
        if (empty($json)) {
            die("The JSON string is empty!");
        }
        // If passed a string, turn it into an array
        if (is_array($json) === false) {
            $json = json_decode($json, true);
        }
        // If a path is included, open that file for handling. Otherwise, use a temp file (for echoing CSV string)
        // if ($csvFilePath !== false) {
        //     $f = fopen($csvFilePath,'w+');
        //     if ($f === false) {
        //         die("Couldn't create the file to store the CSV, or the path is invalid. Make sure you're including the full path, INCLUDING the name of the output file (e.g. '../save/path/csvOutput.csv')");
        //     }
        // }
        // else {
        //     $boolEchoCsv = true;
        //     if ($boolOutputFile === true) {
        //         $boolEchoCsv = false;
        //     }
        //     $strTempFile = 'csvOutput' . date("U") . ".csv";
        //     $f = fopen($strTempFile,"w+");
        // }
        $strTempFile = public_path() . "/upload/csv/" . 'csvOutput' . date("U") . ".csv";
        $f = fopen($strTempFile, "w+");
        $csvFilePath = $strTempFile;
        $firstLineKeys = false;
        foreach ($json as $line) {
            if (empty($firstLineKeys)) {
                $firstLineKeys = array_keys($line);
                fputcsv($f, $firstLineKeys);
                $firstLineKeys = array_flip($firstLineKeys);
            }
            // Using array_merge is important to maintain the order of keys acording to the first element
            fputcsv($f, array_merge($firstLineKeys, $line));
        }
        fclose($f);
        // Take the file and put it to a string/file for output (if no save path was included in function arguments)
        // Delete the temp file
        // unlink($strTempFile);
        // echo $csvFilePath;
        return response()->download($csvFilePath, null, ['Content-Type' => 'text/csv'])->deleteFileAfterSend();
        //return response()->download($file);
    }
    /**
     * It searches all products with w.r.t all given filters
     * @author Mirza Abdullah Izhar
     * @version 1.7.0
     */
    public function search(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'product_name' => 'required|string',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' =>  $validate->errors()
                ], 422);
            }
            $user_lat = $request->lat;
            $user_lon = $request->lon;
            $miles = $request->miles;
            if (isset($miles)) {
                $store_ids =  $this->searchWrtNearByStores($user_lat, $user_lon,  $miles);
            }
            $productName = $request->product_name;
            // $article = Products::query();
            $article = Products::search($productName);
            $article->where('status', 1);
            if (isset($store_ids)) $article->whereIn('user_id', $store_ids['ids']);
            if (isset($request->category_id)) $article->where('category_id', $request->category_id);
            if (isset($request->store_id)) $article->where('user_id', $request->store_id);
            if (isset($request->brand)) $article->where('brand', $request->brand);
            /**
             * For price range
             */
            if (isset($request->min_price)) $article->where('price >', $request->min_price);
            if (isset($request->max_price)) $article->where('price <', $request->max_price);
            /**
             * For weight range
             */
            if (isset($request->min_weight)) $article->where('weight >', $request->min_weight);
            if (isset($request->max_weight)) $article->where('weight <', $request->max_weight);
            $products = $article->paginate(20);
            $pagination = $products->toArray();
            if (!$products->isEmpty()) {
                $products_data = [];
                foreach ($products as $product) {
                    $products_data[] = $this->getProductInfo($product->id);
                }
                unset($pagination['data']);
                return response()->json([
                    'data' => $products_data,
                    'status' => true,
                    'message' => '',
                    'pagination' => $pagination,
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * It takes lat,lon  from user and store,converts them into distaance
     * and gives all store ids within given miles
     */
    public function searchWrtNearByStores($user_lat, $user_lon, $miles)
    {
        $radius =  3958.8;
        $store_data = DB::table('users AS user')->selectRaw("*,
            (  3961 * acos( cos( radians(" . $user_lat . ") ) *
            cos( radians(user.lat) ) *
            cos( radians(user.lon) - radians(" . $user_lon . ") ) +
            sin( radians(" . $user_lat . ") ) *
            sin( radians(user.lat) ) ) )
            AS distance")
            ->having("distance", "<", $radius)
            ->orderBy("distance", "ASC")
            ->get();
        foreach ($store_data as $data) {
            if ($data->distance <= $miles) {
                $store_ids[] = $data->id;
                $latitude2[] = $data->lat;
                $longitude2[] = $data->lon;
            }
        }
        $pm = $this->getDurationBetweenPointsNew($user_lat, $user_lon, $latitude2, $longitude2);
        return [
            'ids' => $store_ids,
            'time' => $pm,
        ];
    }
    /**
     *It will get the duration between given
     *lat,lon
     * @version 1.0.0
     */
    public function getDurationBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $count = count($longitude2);
        for ($i = 0; $i < $count; $i++) {
            $address2 = $latitude2[$i] . ',' . $longitude2[$i];
            $address1 = $latitude1 . ',' . $longitude1;
            //  $address2 = $latitude2 . ',' . $longitude2;
            $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . urlencode($address1) . "&destination=" . urlencode($address2) . "&transit_routing_preference=fewer_transfers&key=AIzaSyBFDmGYlVksc--o1jpEXf9jVQrhwmGPxkM";
            $query = file_get_contents($url);
            $results = json_decode($query, true);
            $distanceString[] = explode(' ', $results['routes'][0]['legs'][0]['distance']['text']);
            $durationString = explode(' ', $results['routes'][0]['legs'][0]['duration']['text']);
            $miles[] = (int)$distanceString[0] * 0.621371;
            $duration[] = implode(",", $durationString);
        }
        // Google Distance Matrix
        // $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$latitude1.",".$longitude1."&destinations=".$latitude2.",".$longitude2."&mode=driving&key=AIzaSyBFDmGYlVksc--o1jpEXf9jVQrhwmGPxkM";
        // return $miles > 1 ? $miles : 1;
        return  $duration;
    }
    /**
     * Update product price from csv file w.r.t their SKU and store_id
     * @author Mirza Abdullah Izhar
     *
     */
    public function updatePriceBulk(Request $request, $delimiter = ',', $filename = '')
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required',
                'store_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'data' => $validator->errors(),
                    'status' => false,
                    'message' => ""
                ], 422);
            }
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                // File Details
                $filename = $file->getClientOriginalName();
                $location = public_path('upload/csv');
                $file->move($location, $filename);
                $filepath = $location . "/" . $filename;
                // Reading file
                $file = fopen($filepath, "r");
                $i = 0;
                while (($filedata = fgetcsv($file, 1000, $delimiter)) !== FALSE) {
                    if ($i == 0) {
                        $i++;
                        continue;
                    };
                    DB::statement('CREATE Temporary TABLE temp_products LIKE products');
                    $db = DB::statement('INSERT INTO `temp_products`( `user_id`, `category_id`,`product_name`, `sku`, `price`, `featured`, `discount_percentage`, `contact`)VALUES (' . $request->store_id . ',' . $filedata[0] . ',' . $filedata[0] . ',' . $filedata[1] . ',3, ' . $filedata[2] . ',1,20,02083541500 )');
                    DB::statement('UPDATE products,temp_products SET products.price = temp_products.price, products.updated_at = "' . Carbon::now() . '" WHERE products.user_id = temp_products.user_id AND products.category_id = temp_products.category_id AND products.sku = temp_products.sku');
                    DB::statement('DROP Temporary TABLE temp_products');
                }
                fclose($file);
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' =>  config('constants.DATA_UPDATED_SUCCESS'),
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }

    public function updatePriceAndQtyBulk(Request $request, $delimiter = ',', $filename = '', $batchSize = 1000)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'file' => 'required',
                'store_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'data' => $validator->errors(),
                    'status' => false,
                    'message' => ""
                ], 422);
            }

            if ($request->hasFile('file')) {
                $file = $request->file('file');

                // File Details
                $filename = $file->getClientOriginalName();
                $location = public_path('upload/csv');
                $file->move($location, $filename);
                $filepath = $location . "/" . $filename;

                // Reading file
                $file = fopen($filepath, "r");
                $i = 0;

                while (($filedata = fgetcsv($file, 1000, $delimiter)) !== FALSE) {
                    if ($i == 0) {
                        $i++;
                        continue;
                    }
                    $catgory_id = $filedata[0];
                    $sku = $filedata[1];
                    $price = $filedata[2];
                    $qty = $filedata[3];

                    // Find product by sku, user_id, category_id and update price and quantity
                    $product = Product::where('user_id', '=', $request->store_id)
                        ->where('sku', '=', $sku)
                        ->where('category_id', '=', $catgory_id)
                        ->first();

                    if ($product) {
                        $product->price = $price;
                        $product->save();

                        $productQty = Qty::where('prod_id', $product->id)->first();

                        if ($productQty) {
                            $productQty->qty = $qty;
                            $productQty->save();
                        }
                    }

                    $i++;
                    if ($i % $batchSize == 0) {
                        usleep(500000); // Wait for 0.5 seconds between batches to avoid overwhelming the database
                    }
                }

                fclose($file);

                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' =>  config('constants.DATA_UPDATED_SUCCESS'),
                ], 200);
            }
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
