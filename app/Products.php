<?php

namespace App;

use Validator;
use Illuminate\Http\Request;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\RattingsController;
use Illuminate\Support\Facades\DB;

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
    
    public static function getProductInfo(int $product_id)
    {
        $product = Products::with('quantity')->find($product_id);
        $product->images = productImages::query()->where('product_id', '=', $product->id)->get();
        $product->category = Categories::find($product->category_id);
        $product->ratting = (new RattingsController())->get_ratting($product_id);
        return $product;
    }

    public static function getProductsById(object $product)
    {
        return Products::where('id', $product->products_id)->where('status', '1')->paginate(20);
    }

    public static function getProductInfoWithQty(int $product_id, int $store_id)
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

    public static function getParentSellerProductsBySellerId(int $sellerid)
    {
        return Products::where('user_id', '=', $sellerid)->where('status', '=', 1)->paginate(20);
    }

    public static function getParentSellerProductsBySellerIdAsc(int $sellerid)
    {
        return Products::query()->where('user_id', '=', $sellerid)->where('status', '=', 1)->orderBy('id', 'Asc')->get();
    }

    public function getProductsByParameters(int $store_id, string $sku, int $catgory_id)
    {
        return  Products::where('user_id', '=', $store_id)
            ->where('sku', '=', $sku)
            ->where('category_id', '=', $catgory_id)
            ->first();
    }

    public static function getProductWeight(int $product_id)
    {
        $product = DB::table('products')
            ->select('weight')
            ->where('id', $product_id)
            ->get();
        return $product[0]->weight;
    }

    public static function getProductVolume(int $product_id)
    {
        $product = DB::table('products')
            ->select(DB::raw('(products.height * products.width * products.length) as volumn'))
            ->where('id', $product_id)
            ->get();
        return $product[0]->volumn;
    }

    public static function getFeaturedProducts(int $store_id)
    {
        return Products::whereHas('user', function ($query) {
            $query->where('is_active', 1);
        })->where('user_id', '=', $store_id)
            ->where('featured', '=', 1)
            ->where('status', '=', 1)
            ->orderByDesc('id')
            ->paginate(10);
    }
}
