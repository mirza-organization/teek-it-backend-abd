<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Drivers extends Model
{
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
}
