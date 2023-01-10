<?php

namespace App;

use Validator;
use Illuminate\Http\Request;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\RattingsController;

class Products extends Model
{
    use Searchable;
    public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'category_id' => 'required',
            'product_name' => 'required|string|max:255',
            'product_description' => 'required|string',
            'color' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'lat' => 'required|string|max:255',
            'lon' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'qty' => 'required|string|max:255'
        ]);
    }

    public static function updateValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'category_id' => 'required',
            'product_name' => 'required|string|max:255',
            'product_description' => 'required|string',
            'color' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'lat' => 'required|string|max:255',
            'lon' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'qty' => 'required|string|max:255'
        ]);
    }

    public static function getProductInfo($product_id)
    {
        $product = Products::with('quantity')->find($product_id);
        $product->images = productImages::query()->where('product_id', '=', $product->id)->get();
        $product->category = Categories::find($product->category_id);
        $product->ratting = (new RattingsController())->get_ratting($product_id);
        return $product;
    }

    public static function getProductInfoWithQty($product_id, $store_id)
    {
        $product = Products::with('quantity')
            ->where('user_id', $store_id)
            ->where('id', $product_id)
            ->first();
        $product->images = productImages::query()->where('product_id', '=', $product->id)->get();
        $product->category = Categories::find($product->category_id);
        $product->ratting = (new RattingsController())->get_ratting($product_id);
        return $product;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quantity()
    {
        return $this->hasOne(Qty::class);
    }
    public function category()
    {
        return $this->belongsTo(Categories::class);
    }
    public function quantities()
    {
        return $this->hasMany(Qty::class);
    }
}