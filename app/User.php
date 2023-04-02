<?php

namespace App;

use Illuminate\Support\Facades\Mail;
use App\Mail\StoreRegisterMail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Http\Request;
use Validator;

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
        'role_id',
        'parent_store_id'
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
    
    public static function getParentAndChildSellers()
    {
       return User::where('is_active', 1)
                    ->whereIn('role_id', [2, 5])
                    ->orderBy('business_name', 'asc')
                    ->get();
    }
    
    public function nearbyUsers($user_lat, $user_lon, $radius)
    {
        $users = User::selectRaw("*, (  3961 * acos( cos( radians(" . $user_lat . ") ) *
            cos( radians(users.lat) ) *
            cos( radians(users.lon) - radians(" . $user_lon . ") ) +
            sin( radians(" . $user_lat . ") ) *
            sin( radians(users.lat) ) ) )
            AS distance")
            ->having("distance", "<", $radius)
            ->orderBy("distance", "ASC")
            ->get();
        return $users;
    }

  public static function sendStoreApprovedEmail(object $user)
    {
        $html = '<html>
            Hi, ' . $user->name . '<br><br>
            Thank you for registering on ' . env('APP_NAME') . '.
            <br>
            Your store has been approved. Please login to the
            <a href="' . env('FRONTEND_URL') . '">Store</a> to update your store
            <br><br><br>
                     </html>';
        $subject = env('APP_NAME') . ': Account Approved!';
        Mail::to($user->email)
            ->send(new StoreRegisterMail($html, $subject));
    }

    public static function activeOrBlockStore(int $user_id, int $status)
    {
        User::where('id', '=', $user_id)->update(['is_active' => $status]);
        if ($status == 1) {
            $user = User::findOrFail($user_id);
            static::sendStoreApprovedEmail($user);
        }
    }
}

