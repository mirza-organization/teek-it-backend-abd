<?php

namespace App\Http\Controllers;

use App\Products;
use App\Qty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Throwable;

class QtyController extends Controller
{
    /**
     *One time use method which will 
     *shift qty in products table
     *to qty table    
     * @version 1.0.0
     */
    public function shiftQtyInProductsToQtyTable()
    {
        try {
            $get_three_columns = Products::select('id', 'user_id', 'qty')
                ->get();
            foreach ($get_three_columns as $column) {
                DB::table('qty')->insert([
                    'users_id' => $column->user_id,
                    'products_id' => $column->id,
                    'qty' => $column->qty,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            return response()->json([
                'data' => [],
                'status' => true,
                'message' =>  config('constants.DATA_INSERTION_SUCCESS')
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
    // Just Checking
    /**
     * It fetches products qty
     * @version 1.0.0
     */
    public function all()
    {
        // ini_set('memory_limit', '1024M'); 
        try {
            $qty = Qty::simplePaginate(10);
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
    /**
     *It will get the store by given id    
     * @version 1.0.0
     */
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
    /**
     * It is used to test API's respose time
     * By sending them bulk requests in a single attempt
     * @version 1.9.0
     */
    public function multiCURL()
    {
        try {
            // *************Multi CURL
            for ($times = 0; $times < 100; $times++) {
                // create both cURL resources
                $ch[$times] = curl_init();
                curl_setopt_array($ch[$times], array(
                    // CURLOPT_URL => 'https://teekitstaging.shop/api/qty/all-big-tbl/33/branch100',
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
    /**
     * It fetches products qty
     * From the big table with 102 cols
     * @version 1.0.0
     */
    public function allBigTbl(Request $request)
    {
        try {
            $validate = Validator::make($request->route()->parameters(), [
                'store_id' => 'required|integer',
                'branch_col_name' => 'required|string'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validate->errors()
                ], 422);
            }
            $branch_tbl_info = DB::table('big_branch_table')->where('store_id', $request->store_id)->get();
            $qty = DB::table($branch_tbl_info[0]->tbl_name)->select('id', 'prod_id', $request->branch_col_name)->paginate(10);
            return response()->json([
                'data' => $qty,
                'status' => true,
                'message' => '',
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
    /**
     * It edits the qty for a child store
     * @version 1.0.0
     */
    public function updateChildQty(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'qty' => 'required|int|min:0',
        ]);
        if ($validatedData->fails()) {
            flash('Invalid data')->error();
            return Redirect::back()->withInput($request->input());
        }
        Qty::updateOrInsert(
            ['users_id' => Auth::id(), 'products_id' => $request->input('product_id')],
            ['qty' => $request->input('qty')]
        );
        // $quantity = Qty::where('child_store_id', Auth::id())
        //     ->where('users_id', $request->input('parent_id'))
        //     ->where('products_id', $request->input('product_id'))->get();
        // if ($quantity->isEmpty()) {
        //     Qty::create([
        //         'child_store_id' => Auth::id(),
        //         'qty' => $request->input('qty'),
        //         'products_id' => $request->input('product_id'),
        //         'users_id' => $request->input('parent_id'),
        //     ]);
        // } else {
        //     Qty::where('child_store_id', Auth::id())
        //         ->where('users_id', $request->input('parent_id'))
        //         ->where('products_id', $request->input('product_id'))->update([
        //             'child_store_id' => Auth::id(),
        //             'qty' => $request->input('qty'),
        //             'products_id' => $request->input('product_id'),
        //             'users_id' => $request->input('parent_id'),
        //         ]);
        // }
        //return redirect()->back();
        return response()->json([
            'status' => 200,
            'error' => 'false',
            'qty' => $request->input('qty')
        ]);
    }
    /**
     * This method will share parent store
     * qty with their child store
     * @version 1.0.0
     */
    public function insertParentQtyToChild(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'parent_store' => 'required|int',
                'child_store' => 'required|int'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validate->errors()
                ], 422);
            }
            $parent_store_data = Qty::where('users_id', $request->parent_store)->get();
            if ($parent_store_data->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.NO_SELLER')
                ], 200);
            }
            foreach ($parent_store_data as $data) {
                $data = Qty::create([
                    'users_id' => $request->child_store,
                    'products_id' => $data->products_id,
                    // 'child_store_id' => $request->child_store,
                    'qty' => $data->qty,
                ]);
            }
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => config('constants.DATA_INSERTION_SUCCESS')
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