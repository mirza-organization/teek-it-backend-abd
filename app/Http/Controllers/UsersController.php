<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Services\JsonResponseCustom;

class UsersController extends Controller
{
    /**
     * It will fetch the curved distance between 2 points
     * Google distance matrix API is consumed
     * @author Mirza Abdullah Izhar
     * @version 2.3.0
     */
    public function getDistanceBetweenPoints($destination_lat, $destination_lon, $origin_lat, $origin_lon)
    {
        $destination_address = $destination_lat . ',' . $destination_lon;
        $origing_address = $origin_lat . ',' . $origin_lon;
        /* Rameesha's URL */
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=" . urlencode($origing_address) . "&destinations=" . urlencode($destination_address) . "&mode=driving&key=AIzaSyD_7jrpEkUDW7pxLBm91Z0K-U9Q5gK-10U";

        $results = json_decode(file_get_contents($url), true);
        dd($results);
        exit;
        $meters = explode(' ', $results['rows'][0]['elements'][0]['distance']['value']);
        $distanceInMiles = (float)$meters[0] * 0.000621;

        $durationInSeconds = explode(' ', $results['rows'][0]['elements'][0]['duration']['value']);
        $durationInMinutes = round((int)$durationInSeconds[0] / 60);
        return ['distance' => $distanceInMiles, 'duration' => $durationInMinutes];
    }
    /**
     * Fetch seller/store information w.r.t ID
     * If seller/store have distance it will return distance
     * @author Mirza Abdullah Izhar
     * @version 2.1.0
     */
    public function getSellerInfo($seller_info, $map_api_result = null)
    {
        $user = $seller_info;
        if (!$user) return null;
        $data_info = array(
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'address_1' => $user->address_1,
            'business_name' => $user->business_name,
            'business_location' => $user->business_location,
            'business_hours' => $user->business_hours,
            'user_img' => $user->user_img,
            'pending_withdraw' => $user->pending_withdraw,
            'total_withdraw' => $user->total_withdraw,
            'parent_store_id' => $user->parent_store_id,
            'is_online' => $user->is_online,
            'roles' => ($user->role_id == 2) ? ['sellers'] : ['child_sellers']
        );
        if (!is_null($map_api_result)) {
            $data_info['distance'] = $map_api_result['distance'];
            $data_info['duration'] = $map_api_result['duration'];
        }
        return $data_info;
    }
    /**
     * Listing of all Sellers/Stores within 5 miles
     * @param lat mandatory
     * @param lon mandatory
     * @author Mirza Abdullah Izhar
     * @version 2.1.0
     */
    public function sellers(Request $request)
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
            if ($request->query('lat') && $request->query('lon')) {
                $users = User::getParentAndChildSellers();
                foreach ($users as $user) {
                    $result = $this->getDistanceBetweenPoints($user->lat, $user->lon, $request->query('lat'), $request->query('lon'));
                    if ($result['distance'] <= 5) $data[] = $this->getSellerInfo($user, $result);
                }
            }
            if (empty($data)) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.NO_STORES_FOUND'),
                    config('constants.HTTP_OK')
                );
            }
            return JsonResponseCustom::getApiResponse(
                $data,
                config('constants.TRUE_STATUS'),
                '',
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
}
