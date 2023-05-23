<?php

namespace App;

use App\Http\Controllers\ProductsController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Validator;
use App\Products;

class Categories extends Model
{
    /**
     * Relations
     */
    public function products()
    {
        return $this->hasMany(Products::class, 'category_id', 'id');
    }
    /**
     * Validators
     */
    public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
            'category_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    }
    /**
     * Helpers
     */
    public static function uploadImg(object $request, string $category_name)
    {
        $file = $request->file('category_image');
        $cat_name = str_replace(' ', '_', $category_name);
        $filename = uniqid("Category_" . $cat_name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
        Storage::disk('spaces')->put($filename, File::get($file));
        if (Storage::disk('spaces')->exists($filename)) { // check file exists in directory or not
            info("file is stored successfully : " . $filename);
        } else {
            info("file is not found :- " . $filename);
        }
        return $filename;
    }

    public static function add(object $request)
    {
        $category = new Categories();
        $category->category_name = $request->category_name;
        if ($request->hasFile('category_image'))
            $category->category_image = static::uploadImg($request, $category->category_name);
        else
            info("Category image is missing");
        $category->save();
        return $category;
    }

    public static function updateCategory(object $request, $category_id)
    {
        $category = Categories::find($category_id);
        $category->category_name = $request->category_name;
        if ($request->hasFile('category_image'))
            $category->category_image = static::uploadImg($request, $category->category_name);
        else
            info("Category image is missing");
        $category->save();
        return $category;
    }

    public static function getAllCategoriesByStoreId(int $store_id)
    {
        $categories_data = Products::leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('user_id', $store_id)
            ->select('products.category_id', 'categories.category_name', 'categories.category_image', 'categories.created_at', 'categories.updated_at')
            ->groupBy('products.category_id', 'categories.category_name', 'categories.category_image', 'categories.created_at', 'categories.updated_at')
            ->get();
        return ($categories_data->isEmpty()) ? [] : $categories_data;
    }

    public static function allCategories()
    {
        return Categories::all();
    }

    public static function getProducts(int $category_id)
    {
        // $storeId = \request()->store_id;
        // if (!empty($storeId)) {
        //     $products = Products::whereHas('user_id', function ($query) {
        //         $query->where('is_active', 1);
        //     })
        //         ->where('category_id', $category_id)
        //         ->where('user_id', $storeId)
        //         ->where('status', 1)
        //         ->paginate();
        //     return $products;
        // }
        $products = Products::where('category_id', $category_id)
            ->where('status', 1)
            ->paginate(10);
        $pagination = $products->toArray();
        if (!$products->isEmpty()) {
            $products_data = [];
            foreach ($products as $product) $products_data[] = (new ProductsController())->getProductInfo($product->id);
            unset($pagination['data']);
            return ['data' => $products_data, 'pagination' => $pagination];
        } else {
            return [];
        }
    }

    public static function getProductsByStoreId(int $category_id, int $store_id)
    {
        $products = Products::whereHas('user', function ($query) {
            $query->where('is_active', 1);
        })
            ->where('category_id', $category_id)
            ->where('user_id', $store_id)
            ->where('status', 1)
            ->paginate(10);
        $pagination = $products->toArray();
        if (!$products->isEmpty()) {
            $products_data = [];
            foreach ($products as $product) $products_data[] = (new ProductsController())->getProductInfo($product->id);
            unset($pagination['data']);
            return ['data' => $products_data, 'pagination' => $pagination];
        } else {
            return [];
        }
    }

    public static function stores(int $category_id)
    {
        $ids = Categories::select('users.id as store_id')
            ->distinct()
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('users', 'products.user_id', '=', 'users.id')
            ->join('qty', 'products.id', '=', 'qty.products_id')
            ->where('qty', '>', 0) //Products Should Be In Stock
            ->where('products.status', '=', 1) //Products Should Be Live
            ->where('users.is_active', '=', 1) //Seller Should Be Active
            ->where('categories.id', '=', $category_id)
            ->pluck('store_id');
        return User::whereIn('id', $ids)->get();
    }
}
