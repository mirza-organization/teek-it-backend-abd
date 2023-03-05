<?php

namespace App\Http\Controllers;

use App\Categories;
use App\Http\Controllers\Auth\AuthController;
use App\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\User;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

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
    public function getSellerInfo($seller_info, $result = null)
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
        if (!is_null($result)) {
            $data_info['distance'] = $result['distance'];
            $data_info['duration'] = $result['duration'];
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
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validate->errors()
                ], 422);
            }
            $data = [];
            if ($request->query('lat') && $request->query('lon')) {
                $lat = $request->query('lat');
                $lon = $request->query('lon');
                $users = User::where('is_active', 1)
                    ->whereIn('role_id', [2, 5])
                    ->orderBy('business_name', 'asc')
                    ->get();
                foreach ($users as $user) {
                    $result = $this->getDistanceBetweenPoints($user->lat, $user->lon, $lat, $lon);
                    if ($result['distance'] <= 5) $data[] = $this->getSellerInfo($user, $result);
                }
            } else {
                // We will remove this block as soon as the iOS is able to send lat, lons
                $data = Cache::remember('sellers', 60, function () {
                    $users = User::where('is_active', 1)
                        ->whereIn('role_id', [2, 5])
                        ->orderBy('business_name', 'asc')
                        ->get();
                    foreach ($users as $user) {
                        $data[] = $this->getSellerInfo($user);
                    }
                    return $data;
                });
            }
            if (count($data) === 0) {
                return response()->json([
                    'stores' => [],
                    'status' => true,
                    'message' => 'No stores found in this area.'
                ], 200);
            }
            return response()->json([
                'data' => $data,
                'status' => true,
                'message' => ''
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
