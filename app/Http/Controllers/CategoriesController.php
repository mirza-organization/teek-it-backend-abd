<?php

namespace App\Http\Controllers;

use App\Categories;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use App\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\User;
use Illuminate\Support\Facades\Validator;
use Throwable;
use App\Services\JsonResponseCustom;
use function PHPUnit\Framework\isEmpty;

class CategoriesController extends Controller
{
    /**
     * Insert's new categories
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    protected $jsonresponse;
    protected $response;
    public function __construct(JsonResponseCustom $jsonresponse)
    {
        $this->response = $jsonresponse;
    }
    public function add(Request $request)
    {
        try {
            $validate = Categories::validator($request);
            if ($validate->fails()) $this->response->getApiResponse($validate->messages(), false, config('constants.VALIDATION_ERROR'), 400);
            $category = Categories::add($request);
            $this->response->getApiResponse($category, true, config('constants.DATA_INSERTION_SUCCESS'), config('constants.HTTP_OK'));
        } catch (Throwable $error) {
            report($error);
            $this->response->getApiResponse(null, false, $error, 500);
        }
        
    }
    /**
     * Update category
     * @version 1.2.0
     */
    public function update(Request $request, $id)
    {
        try{
        $validate = Categories::updateValidator($request);
        if ($validate->fails()) $this->response->getApiResponse($validate->messages(), false, config('constants.VALIDATION_ERROR'), 400);
        $category = Categories::updateCategory($request, $id);
        $this->response->getApiResponse($category, true, config('constants.DATA_UPDATED_SUCCESS'), config('constants.HTTP_OK'));
        }catch (Throwable $error) {
        report($error);
        $this->response->getApiResponse([], false, $error, config('constants.HTTP_SERVER_ERROR'));
        }
    }
    /**
     * List all categories w.r.t store ID or without store ID
     * @version 1.2.0
     */
    public function all()
    {
        try {
            $categories_data = Categories::allCategories();
            if(!empty($categories_data))$this->response->getApiResponse($categories_data, true, '', config('constants.HTTP_OK'));
             else $this->response->getApiResponse([], false, config('constants.NO_RECORD'), config('constants.HTTP_OK'));
            } 
        catch (Throwable $error) {
            report($error);
            $this->response->getApiResponse(null, false, $error, 500);
        }
    }
    public function products($category_id)
    {
        try {
            $products = Categories::product($category_id);
            $pagination = $products->toArray();
            if (!empty($products)) {
                $products_data = [];
                foreach ($products as $product) {
                    $products_data[] = (new ProductsController())->getProductInfo($product->id);
                }
                unset($pagination['data']);
                $this->response->getApiResponse_ext($products_data, true, null, config('constants.HTTP_OK'), 'pagination', $pagination);
            } else {
                $this->response->getApiResponse(null, false, config('constants.NO_RECORD'), config('constants.HTTP_OK'));
            }
        } catch (Throwable $error) {
            report($error);
            $this->response->getApiResponse(null, false, $error, 500);
        }
    }
    /**
     * It will get the stores w.r.t category id 
     * @version 1.0.0
     */
    public function stores(Request $request, $category_id)
    {
        try {
            $validate = Validator::make($request->query(), [
                'lat' => 'required|numeric|between:-90,90',
                'lon' => 'required|numeric|between:-180,180',
            ]);
            if ($validate->fails()) {
                $this->response->getApiResponse(null, false, $validate->errors(), 422);
            }
            if (!Categories::where('id', $category_id)->exists()) {
                $this->response->getApiResponse(null, false, config('constants.NO_RECORD'), 422);
            }
            $data = 0;
            $data = Categories::stores($request, $category_id);
            if (count($data) === 0) {
                $this->response->getApiResponse_ext(null, true, 'No stores found against this category in this area.', config('constants.HTTP_OK'), null, null);
            }
            $this->response->getApiResponse_ext($data, true, '', config('constants.HTTP_OK'), 'stores', null);
        } catch (Throwable $error) {
            report($error);
            $this->response->getApiResponse_ext(null, false, $error, 500, 'stores', null);
            
        }
    }
}