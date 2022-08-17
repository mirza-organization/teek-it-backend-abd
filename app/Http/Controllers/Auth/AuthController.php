<?php

namespace App\Http\Controllers\Auth;

use App\Drivers;
use App\Http\Controllers\ProductsController;
use App\Products;
use App\Utils\Constants\AppConst;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Keys;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;
use JWTAuth;
use Jenssegers\Agent\Agent;
use App\Models\JwtToken;
use Illuminate\Http\Request;
use App\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.verify', ['except' => ['login', 'register', 'verify', 'sellers', 'sellerProducts', 'searchSellerProducts']]);
    }
    /**
     * Register For Mobile App
     * @author Huzaifa Haleem
     * @version 1.1.0
     */
    public function register(Request $request)
    {
        try {
            $validate = User::validator($request);
            if ($validate->fails()) {
                $response = array('data' => $validate->messages(), 'status' => false, 'message' => config('constants.VALIDATION_ERROR'));
                return response()->json($response, 400);
            }
            $role = Role::where('name', $request->get('role'))->first();
            if ($request->get('role') == 'buyer') {
                $is_active = 1;
            } else {
                $is_active = 0;
            }
            $User = User::create([
                'name' => $request->name,
                'l_name' => $request->l_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'business_name' => $request->business_name,
                'business_location' => $request->business_location,
                'lat' => json_decode($request->business_location)->lat,
                'lon' => json_decode($request->business_location)->long,
                'seller_id' => $request->seller_id,
                'postcode' => $request->postal_code,
                'is_active' => $is_active,
                'vehicle_type' => $request->has('vehicle_type') ? $request->vehicle_type : null
            ]);
            if ($User) {
                if ($request->hasFile('user_img')) {
                    $file = $request->file('user_img');
                    $filename = uniqid($request->name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
                    Storage::disk('spaces')->put($filename, File::get($file));
                    if (Storage::disk('spaces')->exists($filename)) {  // check file exists in directory or not
                        info("file is store successfully : " . $filename);
                    } else {
                        info("file is not found :- " . $filename);
                    }
                    $User->user_img = $filename;
                    $User->save();
                }
            }

            $User->roles()->sync($role->id);
            $verification_code = Crypt::encrypt($User->email);

            $FRONTEND_URL = env('FRONTEND_URL');
            $account_verification_link = $FRONTEND_URL . '/auth/verify?token=' . $verification_code;

            $html = '<html>
            Congratulations ' . $User->name . '!<br><br>
            You have successfully registered on ' . env('APP_NAME') . '.
            <br>
            There is just one more step to go. Click on the link below to verify your account so you can start purchasing products on TeekIT today!  <br><br>
                <a href="' . $account_verification_link . '">Verify</a> OR Copy This in your Browser
                ' . $account_verification_link . '
            <br><br><br>
            For more information please visit https://teekit.co.uk/ 
            If you have any further inquiries please email admin@teekit.co.uk
            </html>';

            Mail::send('emails.general', ["html" => $html], function ($message) use ($request, $User) {
                $message->to($request->email, $User->name)
                    ->subject(env('APP_NAME') . ': Account Verification');
            });
            $response = array('status' => true, 'role' => $request->role, 'message' => 'You have registered succesfully! We have sent a verification link to your email address. Please click on the link to activate your account.');
            return response()->json($response, 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['data' => [], 'status' => false, 'message' => config('constants.INVALID_CREDENTIALS')], 401);
            }
            $user = JWTAuth::user();
            if ($user->email_verified_at == null) {
                return response()->json(['data' => [], 'status' => false, 'message' => config('constants.EMAIL_NOT_VERIFIED')], 401);
            }
            if ($user->is_active == 0) {
                return response()->json(['data' => [], 'status' => false, 'message' => config('constants.ACCOUNT_DEACTIVATED')], 401);
            }
            $this->authenticated($request, $user, $token);
            return $this->respondWithToken($token);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }

    public function verify(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validate->fails()) {
            echo "Validation error";
            return;
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $validate->messages()
            ], 400);
        }

        $token = $request->token;

        $verification_token = Crypt::decrypt($request->token);

        $user = User::where('email', $verification_token)->first();
        $email_verified_at = Carbon::now();

        if ($user) {
            if ($user->email_verified_at != null) {
                echo "Account Already verified";
                return;
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => 'Account Already verified'
                ], 200);
            }
            $user->email_verified_at = $email_verified_at;
            $user->is_active = 1;
            $user->save();

            echo "Account successfully verified";
            return;

            return response()->json([
                'data' => [],
                'status' => true,
                'message' => 'Account successfully verified'
            ], 200);
        } else {
            echo "Invalid verification token";
            return;

            return response()->json([
                'data' => [],
                'status' => false,
                'message' => 'Invalid verification token'
            ], 401);
        }
    }

    public function changePassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' =>  $validate->messages()
            ], 400);
        }

        $User = JWTAuth::user();
        if ($User) {
            $User->password = Hash::make($request->password);
            $User->save();
            return response()->json([
                'data' => [],
                'status' => true,
                'message' =>  'Password changed successfully.'
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' =>  'User not found.'
            ], 404);
        }
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = JWTAuth::user();

        $user = User::find($user->id);

        $seller_info = [];
        $seller_info = User::find($user->seller_id);
        $url = URL::to('/');
        $imagePath = $user['user_img'];
        $data_info = array(
            'id' => $user->id,
            'name' => $user->name,
            'l_name' => $user->l_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address_1' => $user->address_1,
            'address_2' => $user->address_2,
            'postal_code' => $user->postal_code,
            'business_name' => $user->business_name,
            'business_phone' => $user->business_phone,
            'business_location' => $user->business_location,
            'business_hours' => $user->business_hours,
            'bank_details' => $user->bank_details,
            'user_img' => $user->user_img,
            'pending_withdraw' => $user->pending_withdraw,
            'total_withdraw' => $user->total_withdraw,
            'is_online' => $user->is_online,
            'last_login' => $user->last_login,
            'seller_info' => $this->get_seller_info($seller_info),
            'roles' => $user->roles->pluck('name'),
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        );
        return response()->json([
            'data' => $data_info,
            'status' => true,
            'message' => config('constants.DATA_UPDATED_SUCCESS')
        ], 200);
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        JWTAuth::parseToken()->invalidate();
        return response()->json([
            'data' => [],
            'status' => true,
            'message' =>  'Successfully logged out.'
        ], 200);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }
    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = JWTAuth::user();
        $seller_info = [];
        $seller_info = User::find($user->seller_id);

        $url = URL::to('/');
        $imagePath = $user['user_img'];

        $data_info = array(
            'id' => $user->id,
            'name' => $user->name,
            'l_name' => $user->l_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'postal_code' => $user->postal_code,
            'address_1' => $user->address_1,
            'address_2' => $user->address_2,
            'is_online' => $user->is_online,
            'business_name' => $user->business_name,
            'business_phone' => $user->business_phone,
            'business_location' => $user->business_location,
            'business_hours' => $user->business_hours,
            'bank_details' => $user->bank_details,
            'last_login' => $user->last_login,
            'roles' => $user->roles->pluck('name'),
            'user_img' => $imagePath,
            'pending_withdraw' => $user->pending_withdraw,
            'total_withdraw' => $user->total_withdraw,
            'vehicle_type' => $user->vehicle_type,
            'seller_info' => $this->get_seller_info($seller_info),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        );
        return response()->json([
            'data' => $data_info,
            'status' => true,
            'message' =>  config('constants.LOGIN_SUCCESS')
        ], 200);
    }
    /**
     * Fetch seller/store information w.r.t ID
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    private function get_seller_info($seller_info)
    {
        $user = $seller_info;
        if (!$user)
            return null;
        $data_info = array(
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'business_name' => $user->business_name,
            'business_location' => $user->business_location,
            'business_hours' => $user->business_hours,
            'pending_withdraw' => $user->pending_withdraw,
            'total_withdraw' => $user->total_withdraw,
            'address_1' => $user->address_1,
            'is_online' => $user->is_online,
            'roles' => $user->roles->pluck('name'),
            'user_img' => $user->user_img
        );
        return $data_info;
    }

    public function get_user($user_id)
    {
        $data_info = $this->get_seller_info(User::find($user_id));
        return $data_info;
    }

    protected function authenticated($request, $user, $token)
    {
        $olduser = $user;
        $user->last_login = date("Y-m-d H:i:s");
        $user->save();

        $agent = new Agent();
        $isDesktop = $agent->isDesktop();
        $isPhone = $agent->isPhone();
        $jwtToken = new JwtToken();
        $jwtToken->user_id = $user->id;
        $jwtToken->token = $token;
        $jwtToken->browser = $agent->browser();
        $jwtToken->platform = $agent->platform();
        $jwtToken->device = $agent->device();
        $mobileHeader = $request->header('x_platform');
        if (isset($mobileHeader) && $mobileHeader == 'mobile') {
            JwtToken::where('user_id', $user->id)->where('phone', 1)->delete();
            $jwtToken->phone = 1;
            $jwtToken->save();
        } else {
            JwtToken::where('user_id', $user->id)->where('desktop', 1)->delete();
            $jwtToken->desktop = 1;
            $jwtToken->save();
        }
    }

    public function updateUser(Request $request)
    {
        $validate = User::updateValidator($request);
        if ($validate->fails()) {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $validate->messages()
            ], 400);
        }
        $user = JWTAuth::user();
        $User = User::find($user->id);
        if ($User) {
            $filename = $User->user_img;
            if ($request->hasFile('user_img')) {
                $file = $request->file('user_img');
                $filename = $file->getClientOriginalName();
                $filename = uniqid($User->id . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
                Storage::disk('user_public')->put($filename, File::get($file));
                if (Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                    info("file is store successfully : " . $filename);
                    $filename = "/user_imgs/" . $filename;
                } else {
                    info("file is not found :- " . $filename);
                }
            }
            $User->name = $request->name;
            $User->l_name = $request->l_name;
            $User->postal_code = $request->postal_code;
            $User->phone = $request->phone;
            $User->address_1 = $request->address_1;
            $User->address_2 = $request->address_2;
            $User->user_img = $filename;
            $User->save();
            $response = $this->me();
            return $response;
            //return response()->json($response, 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }
    }

    public function updateStatus(Request $request)
    {
        $user = User::find(Auth::id());
        $user->is_online = $request->is_online;
        $user->save();
        $response = $this->me();
        return $response;
    }
    /**
     * Listing of all Sellers/Stores
     * @author Mirza Abdullah Izhar
     * @version 1.2.0
     */
    public function sellers()
    {
        try {
            $users = User::with('seller')
                ->where('is_active', '=', 1)->get();
            $data = [];
            foreach ($users as $user) {
                if ($user->hasRole('seller')) {
                    $user->where('is_active', 1);
                    $data[] = $this->get_seller_info($user);
                }
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
    /**
     * Listing of all products w.r.t Seller/Store 'id'
     * @author Mirza Abdullah Izhar
     * @version 1.2.0
     */
    public function sellerProducts($seller_id)
    {
        try {
            $user = User::find($seller_id);
            $data = [];
            if ($user->hasRole('seller')) {
                // $info = $this->get_seller_info($user); 
                $products = Products::query()->where('user_id', '=', $user->id)->where('status', '=', 1)->paginate(20);
                $pagination = $products->toArray();
                if (!$products->isEmpty()) {
                    foreach ($products as $product) {
                        $data[] = (new ProductsController())->get_product_info($product->id);
                    }
                    // $info['products'] = $products_data;
                    unset($pagination['data']);
                    return response()->json([
                        'data' => $data,
                        'status' => true,
                        'message' => '',
                        'pagination' => $pagination
                    ], 200);
                } else {
                    return response()->json([
                        'data' => [],
                        'status' => false,
                        'message' => config('constants.NO_RECORD')
                    ], 200);
                }
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_SELLER')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * Search products w.r.t Seller/Store 'id' & Product Name
     * @author Mirza Abdullah Izhar
     * @version 1.2.0
     */
    public function searchSellerProducts($seller_id, $product_name)
    {
        try {
            $user = User::find($seller_id);
            $data = [];
            if ($user->hasRole('seller')) {
                $products = Products::query()
                    ->where('user_id', '=', $user->id)
                    ->where('status', '=', 1)
                    ->where('product_name', 'LIKE', '%' . $product_name . '%')->paginate();
                $pagination = $products->toArray();
                if (!$products->isEmpty()) {
                    foreach ($products as $product) {
                        $data[] = (new ProductsController())->get_product_info($product->id);
                    }
                    unset($pagination['data']);
                    return response()->json([
                        'data' => $data,
                        'status' => true,
                        'message' => '',
                        'pagination' => $pagination
                    ], 200);
                } else {
                    return response()->json([
                        'data' => [],
                        'status' => false,
                        'message' => config('constants.NO_RECORD')
                    ], 200);
                }
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_SELLER')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }

    public function deliveryBoys()
    {
        try {
            $users = User::query()->where('seller_id', '=', Auth::id())->get();
            $data = [];
            foreach ($users as $user) {
                if ($user->hasRole('delivery_boy')) {
                    $data[] = $this->get_seller_info($user);
                }
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

    public function getDeliveryBoyInfo($delivery_boy_info)
    {
        $user = User::find($delivery_boy_info);
        if (!$user) {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }
        $data_info = array(
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'business_name' => $user->business_name,
            'business_location' => $user->business_location,
            'address_1' => $user->address_1,
            'pending_withdraw' => $user->pending_withdraw,
            'total_withdraw' => $user->total_withdraw,
            'is_online' => $user->is_online,
            'roles' => $user->roles->pluck('name'),
            'user_img' => $user->user_img
        );
        return response()->json([
            'data' => $data_info,
            'status' => true,
            'message' => ''
        ], 200);
    }
    /**
     * Listing of all SECRET KEYS
     * @version 1.0.0
     */
    public function keys()
    {
        try {
            $keys = Keys::all();
            return response()->json([
                'data' => $keys,
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
    /**
     * It will delete user from users table by id
     * It will insert the deleted user data into 'Deleted_users' table
     * @version 1.0.0
     */
    public function userInfoDelete($user_id)
    {
        try {
            $user = User::find($user_id);
            if (!empty($user)) {
                DB::table('deleted_users')->insert([
                    'user_id' =>  $user->id,
                    'postcode' =>  $user->postcode,
                    'created_at' =>   Carbon::now(),
                    'updated_at' =>   Carbon::now(),
                ]);
                $user->delete();
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.ITEM_DELETED'),
                ], 200);
            }
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
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