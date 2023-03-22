<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Validator;
use DB;
use App\Products;


class Categories extends Model
{
    //
    public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
            'category_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    }

    public static function updateValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
            'category_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    }

    public function products()
    {
        return $this->hasMany(Products::class, 'category_id', 'id');
    }
    
    public static function add($request)
    {
        $category = new Categories();
        $category->category_name = $request->category_name;
        if ($request->hasFile('category_image')) {
            $image = $request->file('category_image');
            $file = $image;
            $cat_name = str_replace(' ', '_', $category->category_name);
            $filename = uniqid("Category_" . $cat_name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
            Storage::disk('user_public')->put($filename, File::get($file));
            if (Storage::disk('user_public')->exists($filename)) { // check file exists in directory or not
                info("file is store successfully : " . $filename);
                $filename = "/user_imgs/" . $filename;
            } else {
                info("file is not found :- " . $filename);
            }
            $category->category_image = $filename;
        } else {
            info("Category image is missing");
        }
        $category->save();

        return $category;
    }

    public static function updateCategory($request, $id)
    {
        $category = Categories::find($id);
        $category->category_name = $request->category_name;
        if ($request->hasFile('category_image')) {
            $image = $request->file('category_image');
            $file = $image;
            $cat_name = str_replace(' ', '_', $category->category_name);
            $filename = uniqid("Category_" . $cat_name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
            Storage::disk('user_public')->put($filename, File::get($file));
            if (Storage::disk('user_public')->exists($filename)) { // check file exists in directory or not
                info("file is store successfully : " . $filename);
                $filename = "/user_imgs/" . $filename;
            } else {
                info("file is not found :- " . $filename);
            }
            $category->category_image = $filename;
        }
        $category->save();
        return $category;
    }

    public static function allCategories()
    {
        $categories = "";
        if (\request()->has('store_id')) {
            $storeId = \request()->store_id;
            $categories = Products::leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->where('user_id', $storeId)
                ->select('products.category_id', 'categories.category_name', 'categories.category_image', 'categories.created_at', 'categories.updated_at')
                ->groupBy('products.category_id', 'categories.category_name', 'categories.category_image', 'categories.created_at', 'categories.updated_at')
                ->get();
            return $categories;
        }
        $categories = Categories::all();
        return $categories;
    }

    public static function getCategoryById()
    {

    }

    public static function product($category_id)
    {
        $storeId = \request()->store_id;
        if ($storeId) {
            $products = Products::whereHas('user', function ($query) {
                $query->where('is_active', 1);
            })
                ->where('category_id', $category_id)
                ->where('user_id', $storeId)
                ->where('status', 1)
                ->get();
        } else {
            $products = Products::whereHas('user', function ($query) {
                $query->where('is_active', 1);
            })
                ->where('category_id', $category_id)
                ->where('status', 1)
                ->get();
        }
        $products = $products->paginate();
        return $products;
    }

    public static function stores($category_id)
    {
        $ids = Categories::select('users.id as store_id')
            ->distinct()
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('users', 'products.user_id', '=', 'users.id')
            ->join('qty', 'products.id', '=', 'qty.products_id')
            ->where('qty', '>', 0) //Products Should Be In Stock
            ->where('status', '=', 1) //Products Should Be Live
            ->where('is_active', '=', 1) //Seller Should Be Active
            ->where('categories.id', '=', $category_id)
            ->pluck('store_id');
        $stores = User::whereIn('id', $ids)->get();
        return $stores;
    }
}