<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class DriverController extends Controller
{

    /**
     * @param $id
     * @return mixed
     */
    public function info($id)
    {
        return User::where('id', $id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'delivery_boy');
            })->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name . ' ' . $user->l_name,
                    'lat_lng' => $user->business_location,
                    'phone' => $user->phone
                ];
            });
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function addLatLng(Request $request)
    {
        $data = [
            'business_location' => $request->latlng,
            'lat' => json_decode($request->latlng)->lat,
            'lon' => json_decode($request->latlng)->long
        ];
        return User::where('id', auth()->id())
            ->update($data);
    }
}
