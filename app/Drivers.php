<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Drivers extends Authenticatable implements JWTSubject

{
    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'f_name',
        'l_name',
        'email',
        'phone',
        'password',
        'vehicle_type',
        'vehicle_number',
        'area',
        'lat',
        'lon',
        'account_holders_name',
        'bank_name',
        'sort_code',
        'account_number',
        'driving_licence_name',
        'dob',
        'driving_licence_number'
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}