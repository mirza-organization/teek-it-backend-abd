<?php

namespace App\Http\Controllers;

use App\Categories;
use App\productImages;
use App\Products;
use App\Rattings;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JWTAuth;
use Jenssegers\Agent\Agent;
use App\Models\JwtToken;
use App\User;
use App\Models\Role;
use Crypt;
use Hash;
use Mail;
use Carbon\Carbon;
use Throwable;
use Validator;

class ProductsController extends Controller
{
    //    /**
    //     * Display a listing of the resource.
    //     *
    //     * @return \Illuminate\Http\Response
    //     */
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
    //     * @param  \App\Products  $products
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function show(Products $products)
    //    {
    //        //
    //    }
    //
    //    /**
    //     * Update the specified resource in storage.
    //     *
    //     * @param  \Illuminate\Http\Request  $request
    //     * @param  \App\Products  $products
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function update(Request $request, Products $products)
    //    {
    //        //
    //    }
    //
    //    /**
    //     * Remove the specified resource from storage.
    //     *
    //     * @param  \App\Products  $products
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function destroy(Products $products)
    //    {
    //        //
    //    }
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
        $product->qty = $request->qty;
        $product->user_id = $user_id;
        $product->save();
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
        $product =  $this->get_product_info($product->id);
        return response()->json([
            'data' => $product,
            'status' => true,
            'message' => config('constants.DATA_INSERTION_SUCCESS')
        ], 200);
    }

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
        $product->qty = $request->qty;
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
        $product =  $this->get_product_info($product->id);
        return response()->json([
            'data' => $product,
            'status' => true,
            'message' => config('constants.DATA_UPDATED_SUCCESS')
        ], 200);
    }

    public function get_product_info($product_id)
    {
        $product = Products::find($product_id);
        $product->images = productImages::query()->where('product_id', '=', $product->id)->get();
        $product->category = Categories::find($product->category_id);
        $product->ratting = (new RattingsController())->get_ratting($product_id);
        return $product;
    }
    /**
     * Search products for a specific seller/store
     * @author Mirza Abdullah Izhar
     * @version 1.2.0
     */
    // public function search(Request $request)
    // {
    //     try {
    //         $validate = Validator::make($request->all(), [
    //             'product_name' => 'required'
    //         ]);
    //         if ($validate->fails()) {
    //             return response()->json([
    //                 'data' => $validate->messages(),
    //                 'status' => false,
    //                 'message' => config('constants.VALIDATION_ERROR')
    //             ], 400);
    //         }
    //         $products = Products::query()
    //             ->where('product_name', 'Like', "%" . $request->get('product_name') . "%")
    //             ->paginate();
    //         $pagination = $products->toArray();
    //         if (!empty($products)) {
    //             $products_data = [];
    //             foreach ($products as $product) {
    //                 $products_data[] = $this->get_product_info($product->id);
    //             }
    //             unset($pagination['data']);
    //             return response()->json([
    //                 'data' => $products_data,
    //                 'status' => true,
    //                 'message' => '',
    //                 'pagination' => $pagination,
    //             ], 200);
    //         } else {
    //             return response()->json([
    //                 'data' => [],
    //                 'status' => false,
    //                 'message' => config('constants.NO_RECORD')
    //             ], 200);
    //         }
    //     } catch (Throwable $error) {
    //         report($error);
    //         return response()->json([
    //             'data' => [],
    //             'status' => false,
    //             'message' => $error
    //         ], 500);
    //     }
    // }

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
                    $data = $this->get_product_info($product->id);
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

    public function bulkView(Request $request)
    {
        $ids = explode(',', $request->ids);
        $products = Products::query()->whereIn('id', $ids)->paginate();
        $pagination = $products->toArray();
        if (!empty($products)) {
            $products_data = [];
            foreach ($products as $product) {
                $products_data[] = $this->get_product_info($product->id);
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

    public function sortByPrice()
    {
        try {
            $products = Products::query()->paginate()->sortBy('price');
            $pagination = $products->toArray();
            if (!$products->isEmpty()) {
                $products_data = [];
                foreach ($products as $product) {
                    $products_data[] = $this->get_product_info($product->id);
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

    public function sortByLocation(Request $request)
    {
        $latitude = $request->get('lat');
        $longitude = $request->get('lon');
        $products = Products::select(DB::raw('*, ( 6367 * acos( cos( radians(' . $latitude . ') ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude . ') ) * sin( radians( lat ) ) ) ) AS distance'))->paginate()->sortBy('distance');
        //$products = Products::query()->paginate()->sortBy('price');
        $pagination = $products->toArray();
        if (!empty($products)) {
            $products_data = [];
            $i = 0;
            foreach ($products as $product) {
                if ($i == 50) {
                    continue;
                }
                $i = $i + 1;
                $t = $this->get_product_info($product->id);
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
    public function view($product_id)
    {
        $product = $this->get_product_info($product_id);
        if (!empty($product)) {
            $product->store = User::find($product->user_id);
            return response()->json([
                'data' => $product,
                'status' => true,
                'message' => '',
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
        return $this->get_product_info($product_id);
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
                    $data = $this->get_product_info($product->id);
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
    public function update_qty($product_id, $qty, $operation)
    {
        if ($operation == 'subtract') {
            Products::where('id', '=', $product_id)
                ->decrement('qty', $qty);
        }
        // DB::table('users')->increment('posts', 5);
        // DB::table('users')->decrement('likes', 3);
    }

    public function exportProducts()
    {
        $user_id = Auth::id();
        $products = Products::query()->where('user_id', '=', $user_id)->orderBy('id', 'ASC')->get();
        $all_products = [];
        foreach ($products as $product) {
            $pt = json_decode(json_encode($this->get_product_info($product->id)->toArray()));
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
        //        $all_products['is_valid'] = true;
        //        echo "<pre>";
        //        print_r(json_encode($all_products));die;
        //        print_r($all_products);
        //        die;
        //        $file = time() . '_export.json';
        //        $destinationPath=public_path()."/upload/json/";
        //        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
        //        File::put($destinationPath.$file,json_encode($all_products));
        $destinationPath = public_path() . "/upload/csv/";
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        $file = time() . '_export.csv';
        return  $this->jsonToCsv(json_encode($all_products), $destinationPath . $file, true);
        //return response()->download($destinationPath.$file);
    }

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
     * If condition is satisfied it will search products wrt category id & store id product name
     * Else it will search product by name
     * @author Mirza Abdullah Izhar
     * @version 1.3.0
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
                    'message' =>  $validate->messages()
                ], 400);
            }
            if (isset($request->product_name) && isset($request->category_id) && isset($request->store_id)) {
                $products = Products::query()
                    ->where('product_name', 'Like', "%" . $request->product_name . "%")
                    ->where('category_id', '=', $request->category_id)
                    ->where('user_id', '=', $request->store_id)
                    ->where('status', '=', 1)
                    ->paginate(10);
                $pagination = $products->toArray();
                if (!$products->isEmpty()) {
                    $products_data = [];
                    foreach ($products as $product) {
                        $products_data[] = $this->get_product_info($product->id);
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
            } else {
                $products = Products::query()
                    ->where('product_name', 'Like', "%" . $request->get('product_name') . "%")
                    ->where('status', '=', 1)
                    ->paginate(10);
                $pagination = $products->toArray();
                if (!empty($products)) {
                    $products_data = [];
                    foreach ($products as $product) {
                        $products_data[] = $this->get_product_info($product->id);
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
     * Update product price from csv file w.r.t their SKU and store_id 
     * @author Mirza Abdullah Izhar
     * 
     */
    public function updatePrice(Request $request)
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
                $extension = $file->getClientOriginalExtension();
                $tempPath = $file->getRealPath();
                $fileSize = $file->getSize();
                $mimeType = $file->getMimeType();
                $valid_extension = array("csv");
                $maxFileSize = 2097152;
                if (in_array(strtolower($extension), $valid_extension)) {
                    if ($fileSize <= $maxFileSize) {
                        $location = public_path('upload/csv');
                        $file->move($location, $filename);
                        $filepath = $location . "/" . $filename;
                        // Reading file
                        $file = fopen($filepath, "r");
                        $i = 0;
                        while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                            $num = count($filedata);
                            if ($i == 0) {
                                $i++;
                                continue;
                            };
                            DB::table('products')->where('user_id', $request->store_id)
                                ->where('category_id', $filedata[0])
                                ->where('sku', $filedata[1])
                                ->update(['price' => $filedata[2]]);
                        }
                        fclose($file);
                        return response()->json([
                            'data' => [],
                            'status' => true,
                            'message' =>  config('constants.DATA_UPDATED_SUCCESS'),
                        ], 200);
                    } else {
                        return response()->json([
                            'data' => [],
                            'status' => false,
                            'message' =>  config('constants.FILE_TOO_LARGE'),
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'data' => [],
                        'status' => false,
                        'message' =>  config('constants.INVALID_FILE'),
                    ], 200);
                }
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