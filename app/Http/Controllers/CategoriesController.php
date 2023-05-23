<?php

namespace App\Http\Controllers;

use App\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;
use App\Services\JsonResponseCustom;

class CategoriesController extends Controller
{
    /**
     * Insert's new categories
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function add(Request $request)
    {
        try {
            $validate = Categories::validator($request);
            if ($validate->fails()) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    $validate->errors(),
                    config('constants.HTTP_UNPROCESSABLE_REQUEST')
                );
            }
            $category = Categories::add($request);
            return JsonResponseCustom::getApiResponse(
                $category,
                config('constants.TRUE_STATUS'),
                config('constants.DATA_INSERTION_SUCCESS'),
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseCustom::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * Update category
     * @version 1.2.0
     */
    public function update(Request $request, $category_id)
    {
        try {
            $validate = Categories::validator($request);
            if ($validate->fails()) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    $validate->messages(),
                    config('constants.HTTP_UNPROCESSABLE_REQUEST')
                );
            }
            $category = Categories::updateCategory($request, $category_id);
            return JsonResponseCustom::getApiResponse(
                $category,
                config('constants.TRUE_STATUS'),
                config('constants.DATA_UPDATED_SUCCESS'),
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseCustom::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * List all categories w.r.t store ID or without store ID
     * @version 1.2.0
     */
    public function all()
    {
        try {
            if (request()->has('store_id'))
                $data =  Categories::getAllCategoriesByStoreId(request()->store_id);
            else
                $data = Categories::allCategories();
            if (!empty($data)) {
                return JsonResponseCustom::getApiResponse(
                    $data,
                    config('constants.TRUE_STATUS'),
                    '',
                    config('constants.HTTP_OK')
                );
            } else {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.NO_RECORD'),
                    config('constants.HTTP_OK')
                );
            }
        } catch (Throwable $error) {
            report($error);
            return JsonResponseCustom::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * It will get the products of a specific category 
     * @version 1.9.0
     */
    public function products(Request $request)
     {
        try {
            $validate = Validator::make($request->route()->parameters(), [
                'category_id' => 'required|integer',
            ]);
            if ($validate->fails()) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    $validate->errors(),
                    config('constants.HTTP_UNPROCESSABLE_REQUEST')
                );
            }
            if ($request->store_id)
                $data = Categories::getProductsByStoreId($request->category_id, $request->store_id);
            else
                $data = Categories::getProducts($request->category_id);
            if (!empty($data)) {
                return JsonResponseCustom::getApiResponseExtention(
                    $data['data'],
                    config('constants.TRUE_STATUS'),
                    '',
                    'pagination',
                    $data['pagination'],
                    config('constants.HTTP_OK')
                );
            } else {
                return JsonResponseCustom::getApiResponseExtention(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.NO_RECORD'),
                    'pagination',
                    [],
                    config('constants.HTTP_OK')
                );
            }
        } catch (Throwable $error) {
            report($error);
            return JsonResponseCustom::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
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
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    $validate->errors(),
                    config('constants.HTTP_UNPROCESSABLE_REQUEST')
                );
            }
            $data = [];
            $buyer_lat = $request->lat;
            $buyer_lon = $request->lon;
            $stores = Categories::stores($category_id);
            foreach ($stores as $store) {
                $result = (new UsersController())->getDistanceBetweenPoints($store->lat, $store->lon, $buyer_lat, $buyer_lon);
                if (isset($result['distance']) && $result['distance'] < 5) $data[] = (new UsersController())->getSellerInfo($store, $result);
            }
            if (!empty($data)) {
                return JsonResponseCustom::getApiResponse(
                    $data,
                    config('constants.TRUE_STATUS'),
                    '',
                    config('constants.HTTP_OK')
                );
            } else {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.NO_RECORD'),
                    config('constants.HTTP_OK')
                );
            }
        } catch (Throwable $error) {
            report($error);
            return JsonResponseCustom::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
}
