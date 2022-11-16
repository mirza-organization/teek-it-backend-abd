<?php

namespace App\Http\Controllers;

use App\Qty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class QtyController extends Controller
{
    /**
     * It fetches products qty
     * @version 1.0.0
     */
    public function all()
    {
        // ini_set('memory_limit', '1024M'); 
        try {
            $qty = Qty::simplePaginate(15);
            // Is Null Kaam Nahi Kr Raha
            if (!is_null($qty)) {
                return response()->json([
                    'data' => $qty,
                    'status' => true,
                    'message' => ''
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => true,
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
    public function getByStoreId(Request $request)
    {
        try {
            $validate = Validator::make($request->route()->parameters(), [
                'store_id' => 'required|integer'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validate->errors()
                ], 422);
            }
            $qty = DB::table('products')
                ->select('qty_tests.id', 'products.user_id as store_id', 'qty_tests.qty')
                ->leftJoin('qty_tests', 'products.user_id', '=', 'qty_tests.users_id')
                ->where('products.user_id', '=', $request->store_id)
                ->simplePaginate(10);
            if ($qty) {
                return response()->json([
                    'data' => $qty,
                    'status' => true,
                    'message' => ''
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => true,
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
    /**
     * It will return a specific product's qty
     * @version 1.0.0
     */
    public function getById(Request $request)
    {
        try {
            $validate = Validator::make($request->route()->parameters(), [
                'store_id' => 'required|integer',
                'prod_id' => 'required|integer'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validate->errors()
                ], 422);
            }
            $qty = Qty::where('users_id', $request->store_id)
                ->where('products_id', $request->prod_id)
                ->get();
            if (!is_null($qty)) {
                return response()->json([
                    'data' => Qty::where('users_id', $request->store_id)
                        ->where('products_id', $request->prod_id)
                        ->get(),
                    'status' => true,
                    'message' => ''
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => true,
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
    /**
     * It will update a specific product's qty
     * @version 1.0.0
     */
    public function updateById(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'store_id' => 'required|integer',
                'prod_id' => 'required|integer',
                'qty' => 'required|integer'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' =>  $validate->errors()
                ], 422);
            }
            // Qty::where($query,function($request){
            //     $query->where('users_id', $request->user_id);
            //     $query->where('products_id', $request->prod_id);
            // });
            // echo "hello"; exit;
            DB::table('qty_tests')->where('users_id', $request->store_id)
                ->where('products_id', $request->prod_id)
                ->update(['qty' => $request->qty]);
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => config('constants.DATA_UPDATED_SUCCESS')
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }

    public function multiCURL()
    {
        try {
            // $curl = curl_init();
            // curl_setopt_array($curl, array(
            //     // CURLOPT_URL => 'http://127.0.0.1:8000/api/category/all',
            //     // CURLOPT_URL => 'http://127.0.0.1:8000/api/qty/all',
            //     // CURLOPT_URL => 'https://app.teekit.co.uk/api/category/all',
            //     CURLOPT_URL => 'https://teekitstaging.shop/api/qty/all',
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'GET',
            // ));
    
            // $response = curl_exec($curl);
            
            // curl_close($curl);
            // echo $response;
            // exit;

            // *************Multi CURL
            for ($times = 0; $times < 1; $times++) {
                // create both cURL resources
                $ch[$times] = curl_init();
                curl_setopt_array($ch[$times], array(
                    CURLOPT_URL => 'https://teekitstaging.shop/api/qty/all',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                ));
            } 

            //create the multiple cURL handle
            $mh = curl_multi_init();
            for ($a = 0; $a < count($ch); $a++)
                curl_multi_add_handle($mh, $ch[$a]);

            //execute the multi handle
            do {
                $status = curl_multi_exec($mh, $active);
                if ($active) {
                    curl_multi_select($mh);
                }
            } while ($active && $status == CURLM_OK);

            //close the handles
            for ($a = 0; $a < count($ch); $a++)
                curl_multi_remove_handle($mh, $ch[$a]);
            curl_multi_close($mh);
            print_r($mh);
            exit; 
            return response()->json([
                'data' => $mh,
                'status' => true,
                'message' => config('constants.DATA_UPDATED_SUCCESS')
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
}
