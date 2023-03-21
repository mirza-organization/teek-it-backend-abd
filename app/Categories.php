<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Http\Request;
use Validator;
use DB;
use App\Services\JsonResponseCustom;

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
        }else{
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
        if (\request()->has('store_id')) {
            $storeId = \request()->store_id;
            $categories = "";
            $categories = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->where('user_id', $storeId)
                ->select('products.category_id', 'categories.category_name', 'categories.category_image', 'categories.created_at', 'categories.updated_at')
                ->groupBy('products.category_id', 'categories.category_name', 'categories.category_image', 'categories.created_at', 'categories.updated_at')
                ->get();
            return $categories;
        }
        $categories = "";
        $categories = Categories::all();
        return $categories;
    }
    public static function getCategoryById()
    {

    }
    public static function product($category_id)
    {
        $storeId = \request()->store_id;
        $products = Products::query();
        $products = $products->whereHas('user', function ($query) {
            $query->where('is_active', 1);
        })->where('category_id', '=', $category_id)
            ->where('status', 1);
        if (\request()->has('store_id'))
            $products->where('user_id', $storeId);
        $products = $products->paginate();
        return $products;
    }
    public static function stores($request, $category_id)
    {
        $data = [];
        $buyer_lat = $request->query('lat');
        $buyer_lon = $request->query('lon');
        $ids = DB::table('categories')
            ->select(DB::raw('distinct(user_id) as store_id'))
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('users', 'products.user_id', '=', 'users.id')
            ->join('qty', 'products.id', '=', 'qty.products_id')
            ->where('qty', '>', 0) //Products Should Be In Stock
            ->where('status', '=', 1) //Products Should Be Live
            ->where('is_active', '=', 1) //Seller Should Be Active
            ->where('categories.id', '=', $category_id)
            ->get()->pluck('store_id');
        // $stores = User::whereIn('id', $ids)->get()->toArray();
        $stores = User::whereIn('id', $ids)->get();
        foreach ($stores as $store) {
            $result = (new UsersController())->getDistanceBetweenPoints($store->lat, $store->lon, $buyer_lat, $buyer_lon);
            if (isset($result['distance']) && $result['distance'] < 5)
                $data[] = (new UsersController())->getSellerInfo($store, $result);
        }
        return $data;
    }
}