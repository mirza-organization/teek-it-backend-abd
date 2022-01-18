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
use Validator;

class
ProductsController extends Controller
{
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> revert-2-master
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
//     * Show the form for editing the specified resource.
//     *
//     * @param  \App\Products  $products
//     * @return \Illuminate\Http\Response
//     */
//    public function edit(Products $products)
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




public function add(Request $request){
    $validate = Products::validator($request);

    if ($validate->fails()) {
        $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
        return response()->json($response, 400);
    }
    $user_id=Auth::id();
    $product = new Products();
    $product->category_id=$request->category_id;
    $product->product_name=$request->product_name;
    $product->product_description=$request->product_description;
    $product->color=$request->color;
    $product->size=$request->size;
    $product->lat=$request->lat;
    $product->lng=$request->lng;
    $product->price=$request->price;
    $product->qty=$request->qty;
    $product->user_id=$user_id;

    $product->save();

    if($request->hasFile('images'))
<<<<<<< HEAD
=======
    {
        $images=$request->file('images');
        foreach($images as $image)
        {


            $file =$image;
            $filename=uniqid($user_id."_".$product->id."_".$product->product_name . '_'    ).".".$file->getClientOriginalExtension(); //create unique file name...
            Storage::disk('user_public')->put($filename,File::get($file));
            if(Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                info("file is store successfully : ".$filename);
                $filename ="/user_imgs/".$filename;
            }else {
                info("file is not found :- ".$filename);
            }

            $product_images = new productImages();
            $product_images->product_id = $product->id;
            $product_images->product_image = $filename;
            $product_images->save();
        }
    }
    $product =  $this->get_product_info($product->id);
    $response = array('status' => true, 'message' => 'Product Added', 'data' => $product);
    return response()->json($response, 200);;
}

public function update(Request $request,$id){
    $validate = Products::updateValidator($request);

    if ($validate->fails()) {
        $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
        return response()->json($response, 400);
    }
    $user_id=Auth::id();
    $product = Products::find($id);
    if (empty($product)){
        $response = array('status' => false, 'message' => 'No Product Found', 'data' => null);
        return response()->json($response, 400);
    }

    $product->category_id=$request->category_id;
    $product->product_name=$request->product_name;
    $product->product_description=$request->product_description;
    $product->color=$request->color;
    $product->size=$request->size;
    $product->lat=$request->lat;
    $product->lng=$request->lng;
    $product->price=$request->price;
    $product->qty=$request->qty;
    $product->user_id=$user_id;
    $data = [];
    if($request->hasFile('images'))
    {
$images=$request->file('images');
        foreach($images as $image)
        {


            $file =$image;
            $filename=uniqid($user_id."_".$product->id."_".$product->product_name . '_'    ).".".$file->getClientOriginalExtension(); //create unique file name...
            Storage::disk('user_public')->put($filename,File::get($file));
            if(Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                info("file is store successfully : ".$filename);
                $filename ="/user_imgs/".$filename;
            }else {
                info("file is not found :- ".$filename);
            }

            $product_images = new productImages();
            $product_images->product_id = $product->id;
            $product_images->product_image = $filename;
            $product_images->save();
        }
    }

    $product->save();
    $product =  $this->get_product_info($product->id);
    $response = array('status' => true, 'message' => 'Product Updated', 'data' => $product);
    return response()->json($response, 200);
}

    public function get_product_info($product_id){
        $product = Products::find($product_id);
        $product->images = productImages::query()->where('product_id','=',$product->id)->get();
=======
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
    //     * Show the form for editing the specified resource.
    //     *
    //     * @param  \App\Products  $products
    //     * @return \Illuminate\Http\Response
    //     */
    //    public function edit(Products $products)
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
>>>>>>> revert-2-master
    {
        $images=$request->file('images');
        foreach($images as $image)
        {


            $file =$image;
            $filename=uniqid($user_id."_".$product->id."_".$product->product_name . '_'    ).".".$file->getClientOriginalExtension(); //create unique file name...
            Storage::disk('user_public')->put($filename,File::get($file));
            if(Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                info("file is store successfully : ".$filename);
                $filename ="/user_imgs/".$filename;
            }else {
                info("file is not found :- ".$filename);
            }

            $product_images = new productImages();
            $product_images->product_id = $product->id;
            $product_images->product_image = $filename;
            $product_images->save();
        }
    }
    $product =  $this->get_product_info($product->id);
    $response = array('status' => true, 'message' => 'Product Added', 'data' => $product);
    return response()->json($response, 200);;
}

public function update(Request $request,$id){
    $validate = Products::updateValidator($request);

    if ($validate->fails()) {
        $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
        return response()->json($response, 400);
    }
    $user_id=Auth::id();
    $product = Products::find($id);
    if (empty($product)){
        $response = array('status' => false, 'message' => 'No Product Found', 'data' => null);
        return response()->json($response, 400);
    }

    $product->category_id=$request->category_id;
    $product->product_name=$request->product_name;
    $product->product_description=$request->product_description;
    $product->color=$request->color;
    $product->size=$request->size;
    $product->lat=$request->lat;
    $product->lng=$request->lng;
    $product->price=$request->price;
    $product->qty=$request->qty;
    $product->user_id=$user_id;
    $data = [];
    if($request->hasFile('images'))
    {
$images=$request->file('images');
        foreach($images as $image)
        {


            $file =$image;
            $filename=uniqid($user_id."_".$product->id."_".$product->product_name . '_'    ).".".$file->getClientOriginalExtension(); //create unique file name...
            Storage::disk('user_public')->put($filename,File::get($file));
            if(Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                info("file is store successfully : ".$filename);
                $filename ="/user_imgs/".$filename;
            }else {
                info("file is not found :- ".$filename);
            }

            $product_images = new productImages();
            $product_images->product_id = $product->id;
            $product_images->product_image = $filename;
            $product_images->save();
        }
    }

    $product->save();
    $product =  $this->get_product_info($product->id);
    $response = array('status' => true, 'message' => 'Product Updated', 'data' => $product);
    return response()->json($response, 200);
}

    public function get_product_info($product_id){
        $product = Products::find($product_id);
<<<<<<< HEAD
        $product->images = productImages::query()->where('product_id','=',$product->id)->get();
=======
        $product->images = productImages::query()->where('product_id', '=', $product->id)->get();
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
>>>>>>> revert-2-master
        $product->category = Categories::find($product->category_id);
        $product->ratting = (new RattingsController())->get_ratting($product_id);
        return $product;
    }
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> revert-2-master




    public function search(Request $request){
<<<<<<< HEAD
=======
        $validate = Validator::make($request->all(), [
            'product_name' => 'required'

        ]);

        if ($validate->fails()) {
            $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
            return response()->json($response, 400);
        }

        $products = Products::query()
            ->where('product_name' , 'Like',"%".$request->get('product_name')."%")
            ->paginate();
        $pagination = $products->toArray();
        if (!empty($products)){
            $products_data=[];
            foreach ($products as $product) {

                $products_data[]=$this->get_product_info($product->id);
            }
            unset($pagination['data']);
            $products_data= [
                'data'=>$products_data,
                'status'=>true,
                'message'=>'',
                'pagination'=>$pagination,

            ];
        }else{
            $products_data= [
                'data'=>NULL,
                'status'=>false,
                'message'=>'No Record Found'

            ];
        }
        return response()->json($products_data);

    }
    public function all(){


        $products = Products::whereHas('user',function ($query){
            $query->where('is_active', 1);
        })->where('status',1)->paginate();
        $pagination = $products->toArray();
        if (!empty($products)){
            $products_data=[];
=======
    /**
     * Search products
     * @author Mirza Abdullah Izhar
     * @version 1.2.0
     */
    public function search(Request $request)
    {
>>>>>>> revert-2-master
        $validate = Validator::make($request->all(), [
            'product_name' => 'required'

        ]);

        if ($validate->fails()) {
            $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
            return response()->json($response, 400);
        }

        $products = Products::query()
            ->where('product_name' , 'Like',"%".$request->get('product_name')."%")
            ->paginate();
        $pagination = $products->toArray();
        if (!empty($products)){
            $products_data=[];
            foreach ($products as $product) {

                $products_data[]=$this->get_product_info($product->id);
            }
            unset($pagination['data']);
            $products_data= [
                'data'=>$products_data,
                'status'=>true,
                'message'=>'',
                'pagination'=>$pagination,

            ];
        }else{
            $products_data= [
                'data'=>NULL,
                'status'=>false,
                'message'=>'No Record Found'

            ];
        }
        return response()->json($products_data);

    }
    public function all(){


        $products = Products::whereHas('user',function ($query){
            $query->where('is_active', 1);
        })->where('status',1)->paginate();
        $pagination = $products->toArray();
<<<<<<< HEAD
        if (!empty($products)){
            $products_data=[];
=======
        if (!empty($products)) {
            $products_data = [];
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
>>>>>>> revert-2-master
            foreach ($products as $product) {
                $data = $this->get_product_info($product->id);
                $data->store = User::find($product->user_id);
                $products_data[] = $data;
            }
            unset($pagination['data']);
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> revert-2-master
            $products_data= [
                'data'=>$products_data,
                'status'=>true,
                'message'=>'',
                'pagination'=>$pagination,

            ];
        }else{
            $products_data= [
                'data'=>NULL,
                'status'=>false,
                'message'=>'No Record Found'

            ];
<<<<<<< HEAD
=======
        }

        return response()->json($products_data);

    }
    public function bulkView(Request $request){
$ids = explode(',',$request->ids);

        $products = Products::query()->whereIn('id',$ids)->paginate();
        $pagination = $products->toArray();
        if (!empty($products)){
            $products_data=[];
            foreach ($products as $product) {
//print_r($product);
                $products_data[]=$this->get_product_info($product->id);
            }
            unset($pagination['data']);
            $products_data= [
                'data'=>$products_data,
                'status'=>true,
                'message'=>'',
                'pagination'=>$pagination,

            ];
        }else{
            $products_data= [
                'data'=>NULL,
                'status'=>false,
                'message'=>'No Record Found'

            ];
        }

        return response()->json($products_data);

    }





    public function sortByPrice(){


        $products = Products::query()->paginate()->sortBy('price');
        $pagination = $products->toArray();
        if (!empty($products)){
            $products_data=[];
            foreach ($products as $product) {
//print_r($product);
                $products_data[]=$this->get_product_info($product->id);
            }
            unset($pagination['data']);
            $products_data= [
                'data'=>$products_data,
                'status'=>true,
                'message'=>'',
                'pagination'=>$pagination,

            ];
        }else{
            $products_data= [
                'data'=>NULL,
                'status'=>false,
                'message'=>'No Record Found'

            ];
        }

        return response()->json($products_data);

    }

    public function sortByLocation(Request $request){
$latitude = $request->get('lat');
$longitude = $request->get('lng');
        $products =Products::select(DB::raw('*, ( 6367 * acos( cos( radians('.$latitude.') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( lat ) ) ) ) AS distance'))->paginate()->sortBy('distance');
//        $products = Products::query()->paginate()->sortBy('price');
        $pagination = $products->toArray();
//        return$products;
        if (!empty($products)){
            $products_data=[];
            $i=0;
            foreach ($products as $product) {
                if ($i==50){
                    continue;
                }
                $i=$i+1;
//print_r($product);
                $t = $this->get_product_info($product->id);
                $t->distance = $product->distance;
//                $t->distance = round($product->distance);
                $products_data[]=$t;
            }
            unset($pagination['data']);
            $products_data= [
                'data'=>$products_data,
                'status'=>true,
                'message'=>'',
//                'pagination'=>$pagination,

            ];
        }else{
            $products_data= [
                'data'=>NULL,
                'status'=>false,
                'message'=>'No Record Found'

            ];
        }

        return response()->json($products_data);

    }


public function view($product_id){
$product = $this->get_product_info($product_id);
    if (!empty($product)){
        $products_data= [
            'data'=>$product,
            'status'=>true,
            'message'=>'',

        ];
    }else{
        $products_data= [
            'data'=>NULL,
            'status'=>false,
            'message'=>'No Product Found'

        ];
    }

    return response()->json($products_data);
}


    public function delete($product_id){

        Products::find($product_id)->delete();

        return $this->all();

    }
    public function delete_image($image_id,$product_id){
//    echo $image_id,$product_id;die;
=======
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
>>>>>>> revert-2-master
        }

        return response()->json($products_data);

    }
    public function bulkView(Request $request){
$ids = explode(',',$request->ids);

        $products = Products::query()->whereIn('id',$ids)->paginate();
        $pagination = $products->toArray();
        if (!empty($products)){
            $products_data=[];
            foreach ($products as $product) {
//print_r($product);
                $products_data[]=$this->get_product_info($product->id);
            }
            unset($pagination['data']);
            $products_data= [
                'data'=>$products_data,
                'status'=>true,
                'message'=>'',
                'pagination'=>$pagination,

            ];
        }else{
            $products_data= [
                'data'=>NULL,
                'status'=>false,
                'message'=>'No Record Found'

            ];
        }

        return response()->json($products_data);

    }





    public function sortByPrice(){


        $products = Products::query()->paginate()->sortBy('price');
        $pagination = $products->toArray();
        if (!empty($products)){
            $products_data=[];
            foreach ($products as $product) {
//print_r($product);
                $products_data[]=$this->get_product_info($product->id);
            }
            unset($pagination['data']);
            $products_data= [
                'data'=>$products_data,
                'status'=>true,
                'message'=>'',
                'pagination'=>$pagination,

            ];
        }else{
            $products_data= [
                'data'=>NULL,
                'status'=>false,
                'message'=>'No Record Found'

            ];
        }

        return response()->json($products_data);

    }

    public function sortByLocation(Request $request){
$latitude = $request->get('lat');
$longitude = $request->get('lng');
        $products =Products::select(DB::raw('*, ( 6367 * acos( cos( radians('.$latitude.') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( lat ) ) ) ) AS distance'))->paginate()->sortBy('distance');
//        $products = Products::query()->paginate()->sortBy('price');
        $pagination = $products->toArray();
//        return$products;
        if (!empty($products)){
            $products_data=[];
            $i=0;
            foreach ($products as $product) {
                if ($i==50){
                    continue;
                }
                $i=$i+1;
//print_r($product);
                $t = $this->get_product_info($product->id);
                $t->distance = $product->distance;
//                $t->distance = round($product->distance);
                $products_data[]=$t;
            }
            unset($pagination['data']);
            $products_data= [
                'data'=>$products_data,
                'status'=>true,
                'message'=>'',
//                'pagination'=>$pagination,

            ];
        }else{
            $products_data= [
                'data'=>NULL,
                'status'=>false,
                'message'=>'No Record Found'

            ];
        }

        return response()->json($products_data);

    }


public function view($product_id){
$product = $this->get_product_info($product_id);
    if (!empty($product)){
        $products_data= [
            'data'=>$product,
            'status'=>true,
            'message'=>'',

        ];
    }else{
        $products_data= [
            'data'=>NULL,
            'status'=>false,
            'message'=>'No Product Found'

        ];
    }

    return response()->json($products_data);
}


    public function delete($product_id){

        Products::find($product_id)->delete();

        return $this->all();

<<<<<<< HEAD
    }
    public function delete_image($image_id,$product_id){
//    echo $image_id,$product_id;die;
=======
    public function delete_image($image_id, $product_id)
    {
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
>>>>>>> revert-2-master
        productImages::find($image_id)->delete();
        return $this->get_product_info($product_id);
    }

<<<<<<< HEAD


    public function get_product_price($product_id){
=======
<<<<<<< HEAD


    public function get_product_price($product_id){
        $product = Products::find($product_id);
        if ($product->sale_price > 0 ){
            return $product->sale_price * 1.2;
        }
        return $product->price * 1.2;
    }
    public function get_product_seller_id($product_id){
        return Products::find($product_id)->user_id;
    }


    public function exportProducts(){
        $user_id = Auth::id();

        $products = Products::query()->where('user_id','=',$user_id)->orderBy('id','ASC')->get();
        $all_products = [];
        foreach ($products as $product){
//            echo "<pre>";
=======
    public function get_product_price($product_id)
    {
>>>>>>> revert-2-master
        $product = Products::find($product_id);
        if ($product->sale_price > 0 ){
            return $product->sale_price * 1.2;
        }
        return $product->price * 1.2;
    }
    public function get_product_seller_id($product_id){
        return Products::find($product_id)->user_id;
    }


    public function exportProducts(){
        $user_id = Auth::id();

        $products = Products::query()->where('user_id','=',$user_id)->orderBy('id','ASC')->get();
        $all_products = [];
<<<<<<< HEAD
        foreach ($products as $product){
//            echo "<pre>";
=======
        foreach ($products as $product) {
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
>>>>>>> revert-2-master
            $pt = json_decode(json_encode($this->get_product_info($product->id)->toArray()));
            unset($pt->category);
            unset($pt->ratting);
            unset($pt->id);
            unset($pt->user_id);
<<<<<<< HEAD

=======
<<<<<<< HEAD

            unset($pt->created_at);
            unset($pt->updated_at);
            $temp_img = [];
            if (isset($pt->images)){
                foreach ($pt->images as $img){
                    $temp_img[] = $img->product_image;
                }
            }
            $pt->images = implode(',',$temp_img);

            $all_products[]=$pt;

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

        $destinationPath=public_path()."/upload/csv/";
        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }

        $file = time() . '_export.csv';

       return  $this->jsonToCsv(json_encode($all_products),$destinationPath.$file,true);












//        return response()->download($destinationPath.$file);

    }

    function jsonToCsv ($json, $csvFilePath = false, $boolOutputFile = false) {

=======
>>>>>>> revert-2-master
            unset($pt->created_at);
            unset($pt->updated_at);
            $temp_img = [];
            if (isset($pt->images)){
                foreach ($pt->images as $img){
                    $temp_img[] = $img->product_image;
                }
            }
            $pt->images = implode(',',$temp_img);

            $all_products[]=$pt;

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

        $destinationPath=public_path()."/upload/csv/";
        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }

        $file = time() . '_export.csv';

       return  $this->jsonToCsv(json_encode($all_products),$destinationPath.$file,true);












//        return response()->download($destinationPath.$file);

    }

<<<<<<< HEAD
    function jsonToCsv ($json, $csvFilePath = false, $boolOutputFile = false) {

=======
    function jsonToCsv($json, $csvFilePath = false, $boolOutputFile = false)
    {
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
>>>>>>> revert-2-master
        // See if the string contains something
        if (empty($json)) {
            die("The JSON string is empty!");
        }
<<<<<<< HEAD

=======
<<<<<<< HEAD

=======
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
>>>>>>> revert-2-master
        // If passed a string, turn it into an array
        if (is_array($json) === false) {
            $json = json_decode($json, true);
        }
<<<<<<< HEAD

=======
<<<<<<< HEAD

=======
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
>>>>>>> revert-2-master
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
<<<<<<< HEAD
 $strTempFile = public_path()."/upload/csv/".'csvOutput' . date("U") . ".csv";
            $f = fopen($strTempFile,"w+");
           $csvFilePath = $strTempFile;
=======
<<<<<<< HEAD
 $strTempFile = public_path()."/upload/csv/".'csvOutput' . date("U") . ".csv";
            $f = fopen($strTempFile,"w+");
           $csvFilePath = $strTempFile;
=======
        $strTempFile = public_path() . "/upload/csv/" . 'csvOutput' . date("U") . ".csv";
        $f = fopen($strTempFile, "w+");
        $csvFilePath = $strTempFile;
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
>>>>>>> revert-2-master
        $firstLineKeys = false;
        foreach ($json as $line) {
            if (empty($firstLineKeys)) {
                $firstLineKeys = array_keys($line);
                fputcsv($f, $firstLineKeys);
                $firstLineKeys = array_flip($firstLineKeys);
            }
<<<<<<< HEAD

=======
<<<<<<< HEAD

=======
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
>>>>>>> revert-2-master
            // Using array_merge is important to maintain the order of keys acording to the first element
            fputcsv($f, array_merge($firstLineKeys, $line));
        }
        fclose($f);
<<<<<<< HEAD

=======
<<<<<<< HEAD

        // Take the file and put it to a string/file for output (if no save path was included in function arguments)


        // Delete the temp file
        // unlink($strTempFile);
        // echo $csvFilePath;
        return response()->download($csvFilePath,null,['Content-Type'=>'text/csv'])->deleteFileAfterSend();
        //return response()->download($file);

=======
>>>>>>> revert-2-master
        // Take the file and put it to a string/file for output (if no save path was included in function arguments)


        // Delete the temp file
        // unlink($strTempFile);
        // echo $csvFilePath;
        return response()->download($csvFilePath,null,['Content-Type'=>'text/csv'])->deleteFileAfterSend();
        //return response()->download($file);
<<<<<<< HEAD

=======
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
>>>>>>> revert-2-master
    }
}
