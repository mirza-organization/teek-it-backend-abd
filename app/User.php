<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Http\Request;
use Validator;
use DB;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    // use EntrustUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_verified_at',
        'is_active',
        'name',
        'l_name',
        'email',
        'password',
        'phone',
        'address_1',
        'address_2',
        'postal_code',
        'business_name',
        'business_phone',
        'business_location',
        'business_hours',
        'settings',
        'bank_details',
        'user_img',
        'postal_code',
        'vehicle_type',
        'lat',
        'lon',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
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
        return [
            'name' => $this->name,
            'roles' => $this->roles
        ];
    }

    public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'l_name' => '',
            'postal_code' => '',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:50',
            'business_name' => 'string|max:255',
            'business_location' => 'string|max:255',
            'role' => 'required|string|max:255',
            'address_1' => '',
            'address_2' => '',
        ]);
    }

    public static function updateValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'l_name' => '',
            'postal_code' => '',
            'business_name' => '',
            'business_location' => '',
            'user_img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address_1' => '',
            'address_2' => ''
        ]);
    }
    // Many-to-many relationship
    public function roles()
    {
        return $this->belongsToMany('App\Role', 'role_user');
    }
    public function role()
    {
        return $this->belongsTo('App\Role');
    }
    // Many-to-many relationship
    public function seller()
    {
        return $this->belongsToMany('App\Role', 'role_user')->wherePivot('role_id', 2);
    }
    // Many-to-many relationship
    public function driver()
    {
        return $this->belongsToMany('App\Models\Role', 'role_user')->where('name', 'delivery_boy');
    }
    // One-to-many relationship
    public function orders()
    {
        return $this->hasMany('App\Orders');
    }
}