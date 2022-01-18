<?php

namespace App\Http\Controllers;

use App\Categories;
use Illuminate\Http\Request;
use App\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JWTAuth;
use App\User;
use Crypt;
use Hash;
use Mail;
use Validator;

class CategoriesController extends Controller
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
    //     * @param  \App\Categories  $categories
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function show(Categories $categories)
    //    {
    //        //
    //    }
    //
    //    /**
    //     * Show the form for editing the specified resource.
    //     *
    //     * @param  \App\Categories  $categories
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function edit(Categories $categories)
    //    {
    //        //
    //    }
    //
    //    /**
    //     * Update the specified resource in storage.
    //     *
    //     * @param  \Illuminate\Http\Request  $request
    //     * @param  \App\Categories  $categories
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function update(Request $request, Categories $categories)
    //    {
    //        //
    //    }
    //
    //    /**
    //     * Remove the specified resource from storage.
    //     *
    //     * @param  \App\Categories  $categories
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function destroy(Categories $categories)
    //    {
    //        //
    //    }







    public function add(Request $request)
    {
        $validate = Categories::validator($request);

        if ($validate->fails()) {
            $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
            return response()->json($response, 400);
        }
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
        $response = array('status' => true, 'message' => 'Category Added', 'data' => $category);
        return response()->json($response, 200);
    }

    public function update(Request $request, $id)
    {
        $validate = Categories::updateValidator($request);


        if ($validate->fails()) {
            $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
            return response()->json($response, 400);
        }
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
        $response = array('status' => true, 'message' => 'Category', 'data' => $category);
        return response()->json($response, 200);
    }

    public function all()
    {
        $storeId = \request()->store_id;
        $categories = Categories::query();
        if (\request()->has('store_id')) {
            $categories = $categories->whereHas('products', function ($q) use ($storeId) { 
                print_r($q); exit;
                $q->where('status', '=', 1)
                    ->where('user_id', '=', $storeId);
            });
        }
        $categories = $categories->get();
        if (!empty($categories)) {
            $categories_data = [];
            foreach ($categories as $category) {
                //    $products = Products::query()->where('category_id', '=', $category->id)->get();
                //    if (!empty($products)) {
                //        $products_data = [];
                //        foreach ($products as $product) {
                //            $products_data[] = (new ProductsController())->get_product_info($product->id);
                //        }
                //        $category['products'] = $products_data;
                //
                //    }
                $categories_data[] = $category;
            }
            return response()->json([
                'data' => $categories_data,
                'status' => true,
                'message' => ''
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        }
    }

    public function Products($category_id)
    {
        $storeId = \request()->store_id;
        $products = Products::query();
        $products = $products->whereHas('user', function ($query) {
            $query->where('is_active', 1);
        })->where('category_id', '=', $category_id)
            ->where('status', 1);
        if (\request()->has('store_id')) $products->where('user_id', $storeId);
        $products = $products->paginate();
        $pagination = $products->toArray();
        if (!empty($products)) {
            $products_data = [];
            foreach ($products as $product) {

                $products_data[] = (new ProductsController())->get_product_info($product->id);
            }
            unset($pagination['data']);
            $products_data = [
                'data' => $products_data,
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


    public function stores($category_id): \Illuminate\Http\JsonResponse
    {
        if (!Categories::where('id', $category_id)->exists()) {
            return response()->json(['message' => 'Category Id is invalid.'], 422);
        }
        $ids = DB::table('categories')
            ->select(DB::raw('distinct(user_id) as store_id'))
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('users', 'products.user_id', '=', 'users.id')
            ->where('qty', '>', 0)
            ->where('status', '=', 1)
            ->where('is_active', '=', 1)
            ->where('categories.id', '=', $category_id)
            ->get()->pluck('store_id');
        $stores = User::whereIn('id', $ids)->get()->toArray();
        return response()->json(['stores' => $stores], 200);
    }
}
