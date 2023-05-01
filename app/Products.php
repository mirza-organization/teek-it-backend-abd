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
    /**
     * Relations
     */
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
    /**
     * Validators
     */
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
    /**
     * Helpers
     */
    public static function getProductInfo(int $product_id)
    {
        $product = Products::with('quantity')->find($product_id);
        $product->images = productImages::query()->where('product_id', '=', $product->id)->get();
        $product->category = Categories::find($product->category_id);
        $product->ratting = (new RattingsController())->get_ratting($product_id);
        return $product;
    }

    public static function getProductsById(object $product_id)
    {
        return Products::where('id', $product_id->products_id)
            ->where('status', '1')
            ->get();
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

    public static function getParentSellerProducts(int $seller_id)
    {
        return Products::where('user_id', '=', $seller_id)->where('status', '=', 1)->paginate(20);
    }

    public static function getParentSellerProductsAsc(int $seller_id)
    {
        return Products::where('user_id', '=', $seller_id)->where('status', '=', 1)->orderBy('id', 'Asc')->get();
    }

    public static function getParentSellerProductsDesc(int $seller_id)
    {
        return Products::where('user_id', '=', $seller_id)->where('status', '=', 1)->orderBy('id', 'Desc')->get();
    }

    public static function getChildSellerProducts(int $child_seller_id)
    {
        $parent_seller_id = User::find($child_seller_id)->parent_store_id;
        $qty = Qty::where('users_id', $child_seller_id)->first();
        if (!empty($qty)) {
            return Products::where('user_id', $parent_seller_id)
            ->join('qty', 'products.id', '=', 'qty.products_id')
            ->select('products.id as prod_id', 'products.user_id as parent_seller_id','products.category_id','products.product_name','products.price','products.feature_img','qty.id as qty_id', 'qty.users_id as child_seller_id', 'qty.qty')
            ->where('qty.users_id', $child_seller_id);
            // ->paginate(20);
        } else {
            return Products::with('quantity')->where('user_id', $parent_seller_id);
        }
    }

    public function getProductsByParameters(int $store_id, string $sku, int $catgory_id)
    {
        return Products::where('user_id', '=', $store_id)
            ->where('sku', '=', $sku)
            ->where('category_id', '=', $catgory_id)
            ->first();
    }

    public static function getProductWeight(int $product_id)
    {
        $product = Products::select('weight')->where('id', $product_id)->get();
        return $product[0]->weight;
    }

    public static function getProductVolume(int $product_id)
    {
        $product = Products::select(DB::raw('(products.height * products.width * products.length) as volumn'))
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

    public static function getActiveProducts()
    {
        return Products::whereHas('user', function ($query) {
            $query->where('is_active', 1);
        })->where('status', 1)->paginate();
    }

    public static function getProductsByLocation(object $request)
    {
        $latitude = $request->get('lat');
        $longitude = $request->get('lon');
        return Products::selectRaw('*, ( 6367 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
        ->orderBy('distance')
        ->paginate();
    }

    //public static function getBulkProducts(object $request){
    //  $latitude = $request->get('lat');
    //$longitude = $request->get('lon');
    //return Products::select(DB::raw('*, ( 6367 * acos( cos( radians(' . $latitude . ') ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude . ') ) * sin( radians( lat ) ) ) ) AS distance'))->paginate()->sortBy('distance');
    //}

    public static function getBulkProducts($request)
    {
        $ids = explode(',', $request->ids);
        return Products::query()->whereIn('id', $ids)->paginate();
    }
}
