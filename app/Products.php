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
    public static function validator(Object $request)
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

    public static function updateValidator(Object $request)
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

    public static function getProductInfo($product_id)
    {
        $qty = Products::with('quantity')
            ->where('id', $product_id)
            ->first();
        $quantity = $qty->quantity->qty;
        $product = Products::with('quantity')
            ->select(['*', DB::raw("$quantity as qty")])
            ->find($product_id);
        $product->images = productImages::query()->where('product_id', '=', $product_id)->get();
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

    public static function getSellerProductsBySellerId(int $sellerid)
    {
        return Products::query()->where('user_id', '=', $sellerid)->where('status', '=', 1)->paginate(20);
    }

    public static function getSellerProductsBySellerIdAsc(int $sellerid)
    {
        return Products::query()->where('user_id', '=', $sellerid)->where('status', '=', 1)->orderBy('id', 'Asc')->get();
    }

    public static function getProductsByParameters(int $store_id, string $sku, int $catgory_id)
    {
        return  Products::where('user_id', '=', $store_id)
        ->where('sku', '=', $sku)
        ->where('category_id', '=', $catgory_id)
        ->first();
    }

    public static function getProductWeight(int $product_id)
    {
        $product = Products::select('weight')
            ->where('id', $product_id)
            ->get();
        return $product[0]->weight;
    }

    public static function getProductVolume(int $product_id){
        $product = Products::selectRaw('(height * width * length) as volume')
            ->where('id', $product_id)
            ->get();
      return $product[0]->volumn;
    }

    public static function getSellerProductsBySellerId($sellerid)
    {
        return Products::query()->where('user_id', '=', $sellerid)->where('status', '=', 1)->paginate(20);

    }
    
    public static function getSellerProductsBySellerIdAsc($sellerid)
    {
        return Products::query()->where('user_id', '=', $sellerid)->where('status', '=', 1)->orderBy('id', 'Asc')->get();
    }
    
    public function getProductsByParameters($store_id, $sku, $catgory_id)
    {
        return  Products::where('user_id', '=', $store_id)
        ->where('sku', '=', $sku)
        ->where('category_id', '=', $catgory_id)
        ->first();
    }
    
    public static function getProductWeight($product_id)
    {
        $product = DB::table('products')
            ->select('weight')
            ->where('id', $product_id)
            ->get();
        return $product[0]->weight;
    }
    
    public static function getProductVolume($product_id){
        $product = DB::table('products')
            ->select(DB::raw('(products.height * products.width * products.length) as volumn'))
            ->where('id', $product_id)
            ->get();
        return $product[0]->volumn;
    }
    
public static function getFeaturedProducts($store_id)
    {
        return Products::whereHas('user', function ($query) {
            $query->where('is_active', 1);
        })->where('user_id', '=', $store_id)
            ->where('featured', '=', 1)
            ->where('status', '=', 1)
            ->orderByDesc('id')
            ->paginate(10);
    }

    public static function getActiveProducts(){
        return Products::whereHas('user', function ($query) {
            $query->where('is_active', 1);
        })->where('status', 1)->paginate();
    }
    
    public static function getProductsByLocation(object $request){
        $latitude = $request->get('lat');
        $longitude = $request->get('lon');
        $products = Products::selectRaw('*, ( 6367 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
        ->orderBy('distance')
        ->paginate();
      return $products;
    }
    
    //public static function getBulkProducts(object $request){
      //  $latitude = $request->get('lat');
        //$longitude = $request->get('lon');
        //return Products::select(DB::raw('*, ( 6367 * acos( cos( radians(' . $latitude . ') ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude . ') ) * sin( radians( lat ) ) ) ) AS distance'))->paginate()->sortBy('distance');
    //}
    
    public static function getBulkProducts($request){
        $ids = explode(',', $request->ids);
        return Products::query()->whereIn('id', $ids)->paginate();
    }

}