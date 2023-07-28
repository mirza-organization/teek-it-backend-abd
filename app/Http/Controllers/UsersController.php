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
        $origing_address = $origin_lat . ',' . $origin_lon;
        $destination_address = $destination_lat . ',' . $destination_lon;
        /* Rameesha's URL */
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=" . urlencode($origing_address) . "&destinations=" . urlencode($destination_address) . "&mode=driving&key=AIzaSyD_7jrpEkUDW7pxLBm91Z0K-U9Q5gK-10U";
        // dd($url);
        $results = json_decode(file_get_contents($url), true);
        $meters = explode(' ', $results['rows'][0]['elements'][0]['distance']['value']);
        $distanceInMiles = (float)$meters[0] * 0.000621;

        $durationInSeconds = explode(' ', $results['rows'][0]['elements'][0]['duration']['value']);
        $durationInMinutes = round((int)$durationInSeconds[0] / 60);
        return ['distance' => $distanceInMiles, 'duration' => $durationInMinutes];
    }

    // Sending Multiple requests to Google Matrix at a time
    public function getDistanceForMultipleDestinations(float $origin_lat, float $origin_lon, array $destinations, int $miles)
    {
        // dd($destinations);
        $origing_address = $origin_lat . ',' . $origin_lon;
        $destinations_addresses = implode('|', $destinations['users_coordinates']);
        // dd($destinations_addresses);
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=" . urlencode($origing_address) . "&destinations=" . urlencode($destinations_addresses) . "&mode=driving&key=AIzaSyD_7jrpEkUDW7pxLBm91Z0K-U9Q5gK-10U";
        // dd($url);
        $results = json_decode(file_get_contents($url), true);
        // dd($results);
        $distanceData = [];
        $user_data = [];
        foreach ($results['rows'] as $row) {
            foreach ($row['elements'] as $key => $element) {
                // dd($element);
                $meters = explode(' ', $element['distance']['value']);
                $distanceInMiles = (float)$meters[0] * 0.000621;

                $durationInSeconds = explode(' ', $element['duration']['value']);
                $durationInMinutes = round((int)$durationInSeconds[0] / 60);
                if ($distanceInMiles <= $miles)
                {
                    $distanceData = [
                        // 'store_id' => $destinations['users'][$key]->id,
                        'distance' => $distanceInMiles,
                        'duration' => $durationInMinutes
                    ];
                    $user_data[] = $this->getSellerInfo($destinations['users'][$key], $distanceData);
                }
            }
        }
        return $user_data;
    }


    /**
     * Fetch seller/store information w.r.t ID
     * If seller/store have distance it will return distance
     * @author Mirza Abdullah Izhar
     * @version 2.1.0
     */
    public function getSellerInfo(Object $seller_info, array $map_api_result = null)
    {
        if (!$seller_info) return null;
        $data = array(
            'id' => $seller_info->id,
            'name' => $seller_info->name,
            'email' => $seller_info->email,
            'address_1' => $seller_info->address_1,
            'business_name' => $seller_info->business_name,
            'business_location' => $seller_info->business_location,
            'business_hours' => $seller_info->business_hours,
            'user_img' => $seller_info->user_img,
            'pending_withdraw' => $seller_info->pending_withdraw,
            'total_withdraw' => $seller_info->total_withdraw,
            'parent_store_id' => $seller_info->parent_store_id,
            'is_online' => $seller_info->is_online,
            'roles' => ($seller_info->role_id == 2) ? ['sellers'] : ['child_sellers']
        );
        if (!empty($map_api_result)) {
            $data['distance'] = $map_api_result['distance'];
            $data['duration'] = $map_api_result['duration'];
        }
        return $data;
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
            $destination_data = [];
            $destination_users_data = [];
            $destination_users_coordinates = [];
            // dd(Cache::flush());
            $users = Cache::remember('sellers', now()->addDay(), function () {
                return User::getParentAndChildSellers();
            });
            // dd($users);
            $pagination = $users->toArray();
            unset($pagination['data']);

            $chunk_size = 25;
            $total_users = $users->count();
            $remaining_users = $total_users;
            $offset = 0;
            $inner_loop_index = 0;
            while ($remaining_users > 0) {
                // The min() function will return the minimum value of both variables 
                $current_chunk_size = min($chunk_size, $remaining_users);
                // $offset === index number of the array, $current_chunk_size === size limit of the returned slice
                $current_users = $users->slice($offset, $current_chunk_size);
                $users_to_loop = count($current_users);
                while ($users_to_loop > 0) {
                    $destination_users_data[] = $current_users[$inner_loop_index];
                    $destination_users_coordinates[] = $current_users[$inner_loop_index]->lat . ',' . $current_users[$inner_loop_index]->lon;
                    ++$inner_loop_index;
                    --$users_to_loop;
                }
                $destination_data['users'] = $destination_users_data;
                $destination_data['users_coordinates'] = $destination_users_coordinates;

                $temp = $this->getDistanceForMultipleDestinations($request->query('lat'), $request->query('lon'), $destination_data, 5);
                if(!empty($temp)) $data = $temp;
                $offset += $current_chunk_size;
                $remaining_users -= $current_chunk_size;
                $inner_loop_index = $offset;
            }

            if (empty($data)) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.NO_STORES_FOUND'),
                    config('constants.HTTP_OK')
                );
            }

            return JsonResponseCustom::getApiResponseExtention(
                $data,
                config('constants.TRUE_STATUS'),
                '',
                'pagination',
                $pagination,
                config('constants.HTTP_OK')
            );
            // foreach ($users as $user) {
            //     // dd($user);
            //     // $result = $this->getDistanceBetweenPoints($user->lat, $user->lon, $request->query('lat'), $request->query('lon'));
            //     $result = $this->getDistanceForMultipleDestinations($request->query('lat'), $request->query('lon'), $destinationCoordinates);
            //     dd($result);
            //     if ($result['distance'] <= 5) $data[] = $this->getSellerInfo($user, $result);
            // }
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
