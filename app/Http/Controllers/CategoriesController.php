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
use Psy\Command\WhereamiCommand;
use Throwable;
use Validator;

class CategoriesController extends Controller
{
    /**
     * Insert's new categories
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function add(Request $request)
    {
        $validate = Categories::validator($request);
        if ($validate->fails()) {
            return response()->json([
                'data' => $validate->messages(),
                'status' => false,
                'message' => config('constants.VALIDATION_ERROR')
            ], 400);
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
        return response()->json([
            'data' => $category,
            'status' => true,
            'message' => config('constants.DATA_INSERTION_SUCCESS')
        ], 200);
    }
    /**
     * Update category
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function update(Request $request, $id)
    {
        $validate = Categories::updateValidator($request);
        if ($validate->fails()) {
            return response()->json([
                'data' =>  $validate->messages(),
                'status' => false,
                'message' => config('constants.VALIDATION_ERROR')
            ], 400);
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
        return response()->json([
            'data' =>  $category,
            'status' => true,
            'message' => config('constants.DATA_UPDATED_SUCCESS')
        ], 200);
    }
    /**
     * List all categories w.r.t store ID or without store ID
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function all()
    {
        try {
            $storeId = \request()->store_id;
            $categories = Categories::query();
            if (\request()->has('store_id')) {
                $categories = DB::table('products')
                    ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                    ->where('user_id', $storeId)
                    ->select('products.category_id', 'categories.category_name', 'categories.category_image', 'categories.created_at', 'categories.updated_at')
                    ->groupBy('products.category_id', 'categories.category_name', 'categories.category_image', 'categories.created_at', 'categories.updated_at')
                    ->get();
                if (!$categories->isEmpty()) {
                    return response()->json([
                        'data' => $categories,
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
            $categories = $categories->get();
            if (!$categories->isEmpty()) {
                $categories_data = [];
                foreach ($categories as $category) {
                    //    $products = Products::query()->where('category_id', '=', $category->id)->get();
                    //    if (!empty($products)) {
                    //        $products_data = [];
                    //        foreach ($products as $product) {
                    //            $products_data[] = (new ProductsController())->getProductInfo($product->id);
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
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }

    public function products($category_id)
    {
        try {
            $storeId = \request()->store_id;
            $products = Products::query();
            $products = $products->whereHas('user', function ($query) {
                $query->where('is_active', 1);
            })->where('category_id', '=', $category_id)
                ->where('status', 1);
            if (\request()->has('store_id')) $products->where('user_id', $storeId);
            $products = $products->paginate();
            $pagination = $products->toArray();
            if (!$products->isEmpty()) {
                $products_data = [];
                foreach ($products as $product) {
                    $products_data[] = (new ProductsController())->getProductInfo($product->id);
                }
                unset($pagination['data']);
                return response()->json([
                    'data' => $products_data,
                    'status' => true,
                    'message' => '',
                    'pagination' => $pagination
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD'),
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

    public function stores($category_id): \Illuminate\Http\JsonResponse
    {
        try {

            if (!Categories::where('id', $category_id)->exists()) {
                return response()->json(['data' => [], 'status' => false, 'message' => config('constants.NO_RECORD')], 422);
            }
            $ids = DB::table('categories')
                ->select(DB::raw('distinct(user_id) as store_id'))
                ->join('products', 'categories.id', '=', 'products.category_id')
                ->join('users', 'products.user_id', '=', 'users.id')
                ->join('qty', 'products.id', '=', 'qty.products_id')
                ->where('qty', '>', 0)
                ->where('status', '=', 1)
                ->where('is_active', '=', 1)
                ->where('categories.id', '=', $category_id)
                ->get()->pluck('store_id');
            $stores = User::whereIn('id', $ids)->get()->toArray();
            return response()->json([
                'stores' => $stores,
                'status' => true,
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'stores' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
}