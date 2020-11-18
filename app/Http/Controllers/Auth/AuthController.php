<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\ProductsController;
use App\Products;
use App\Utils\Constants\AppConst;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use JWTAuth;
use Jenssegers\Agent\Agent;
use App\Models\JwtToken;
use Illuminate\Http\Request;
use App\User;
use App\Models\Role;
use Crypt;
use Hash;
use Mail;
use Carbon\Carbon;
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
        $this->middleware('jwt.verify', ['except' => ['login', 'register', 'verify']]);
    }

    public function register(Request $request)
    {
        $validate = User::validator($request);

        if ($validate->fails()) {
            $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
            return response()->json($response, 400);
        }

        $role = Role::where('name', $request->get('role'))->first();
        if ($request->get('role') == 'buyer') {
            $is_active = 1;
        } else {
            $is_active = 0;
        }
        $User = User::create([
            'name' => $request->get('name'),
            'l_name' => $request->l_name,
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'business_name' => $request->business_name,
            'business_location' => $request->business_location,
            'seller_id' => $request->seller_id,
            'postal_code' => $request->postal_code,
            'is_active' => $is_active,
            'vehicle_type' => $request->has('vehicle_type') ? $request->vehicle_type : null
        ]);

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
        }
        $User->user_img = $filename;
        $User->save();

        $User->roles()->sync($role->id);

        $verification_code = Crypt::encrypt($User->email);

        $FRONTEND_URL = env('FRONTEND_URL');

        $account_verification_link = $FRONTEND_URL . '/auth/verify?token=' . $verification_code;

        $html = '<html>
            Hi, ' . $User->name . '<br><br>

            Thank you for registering on ' . env('APP_NAME') . '.

<br>
            Here is your account verification link. Click on below link to verify you account. <br><br>
            <a href="' . $account_verification_link . '">Verify</a> OR Copy This in your Browser
            ' . $account_verification_link . '
<br><br><br>
        </html>';

        Mail::send('emails.general', ["html" => $html], function ($message) use ($request, $User) {
            $message->to($request->email, $User->name)
                ->subject(env('APP_NAME') . ': Account Verification');
        });


        $response = array('status' => true, 'role' => $request->role, 'message' => 'You are registered successfully, check email and click on verification link to activate your account.');
        return response()->json($response, 200);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {


        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['status' => false, 'message' => 'Invalid credentials'], 401);
        }

        $user = JWTAuth::user();
        // if ($user->email_verified_at == null) {
        //     return response()->json(['status' => false, 'message' => 'Email not verified, verify your email first.'], 401);
        // }

        // if ($user->is_active == 0) {
        //     return response()->json(['status' => false, 'message' => 'You are deactivated, kindly contact admin.'], 401);
        // }
        $this->authenticated($request, $user, $token);
        return $this->respondWithToken($token);
    }

    public function verify(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'token' => 'required'
        ]);

        if ($validate->fails()) {
            $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
            echo "Validation error";
            return;
            return response()->json($response, 400);
        }

        $token = $request->token;

        $verification_token = Crypt::decrypt($request->token);

        $user = User::where('email', $verification_token)->first();
        $email_verified_at = Carbon::now();

        if ($user) {
            if ($user->email_verified_at != null) {

                $response = array('status' => false, 'message' => 'Account Already verified');
                echo "Account Already verified";
                return;
                return response()->json($response, 200);
            }
            $user->email_verified_at = $email_verified_at;
            $user->save();

            $response = array('status' => true, 'message' => 'Account successfully verified');

            echo "Account successfully verified";
            return;
            return response()->json($response, 200);

        } else {
            $response = array('status' => false, 'message' => 'Invalid verification token');

            echo "Invalid verification token";
            return;
            return response()->json($response, 401);
        }
    }

    public function changePassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
            return response()->json($response, 400);
        }

        $User = JWTAuth::user();
        if ($User) {
            $User->password = Hash::make($request->password);
            $User->save();
            $response = array('status' => true, 'message' => 'Password changed successfully.');
            return response()->json($response, 200);
        } else {
            $response = array('status' => false, 'message' => 'User not found');
            return response()->json($response, 404);
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
            'expires_in' => JWTAuth::factory()->getTTL() * 60,);
        $user_arr = [
            'data' => $data_info,
            'status' => true,
            'message' => AppConst::updateMsg

        ];

        return response()->json($user_arr);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        JWTAuth::parseToken()->invalidate();
        return response()->json(['status' => true, 'message' => 'Successfully logged out']);
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
            'seller_info' => $this->get_seller_info($seller_info),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,);
        $user_arr = [
            'data' => $data_info,
            'status' => true,
            'message' => AppConst::loginSuccessMsg

        ];
        return response()->json($user_arr);
    }

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
//        echo date("Y-m-d H:i:s");die;
//        print_r($user);
        $user->last_login = date("Y-m-d H:i:s");
        $user->save();
//die;
        $agent = new Agent();

        $isDesktop = $agent->isDesktop();
        $isPhone = $agent->isPhone();
        $jwtToken = new JwtToken();
        $jwtToken->user_id = $user->id;
        $jwtToken->token = $token;
        $jwtToken->browser = $agent->browser();;
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
            $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
            return response()->json($response, 400);
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

            $User->address_1 = $request->address_1;
            $User->address_2 = $request->address_2;
            $User->user_img = $filename;
            $User->save();

            $response = $this->me();
            return $response;
//            return response()->json($response, 200);
        } else {
            $response = array('status' => false, 'message' => 'User not found.');
            return response()->json($response, 404);
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

    public function sellers()
    {
        $users = User::with('seller')
            ->where('is_active', '=', 1)->get();
        $data = [];
        foreach ($users as $user) {
            if ($user->hasRole('seller')) {
                $user->where('is_active', 1);
                $data[] = $this->get_seller_info($user);
            }
        }
        $user_arr = [
            'data' => $data,
            'status' => true,
            'message' => ''

        ];

        return response()->json($user_arr, 200);
    }

    public function seller_products($seller_id)
    {
        $user = User::find($seller_id);
        $data = [];
        if ($user->hasRole('seller')) {
            $info = $this->get_seller_info($user);
            $products = Products::query()->where('user_id', '=', $user->id)->paginate();

            $pagination = $products->toArray();
            if (!empty($products)) {
                $products_data = [];
                foreach ($products as $product) {

                    $products_data[] = (new ProductsController())->get_product_info($product->id);
                }

                $info['products'] = $products_data;
                unset($pagination['data']);
                $products_data = [
                    'data' => $products_data,
                    'status' => true,
                    'message' => '',
                    'pagination' => $pagination,

                ];
                $user_arr = $products_data;
            } else {
                $user_arr = [
                    'data' => null,
                    'status' => false,
                    'message' => 'No Data Found'

                ];
            }


            return response()->json($user_arr, 200);
        }
    }


    public function delivery_boys()
    {
        $users = User::query()->where('seller_id', '=', Auth::id())->get();
        $data = [];
        foreach ($users as $user) {
            if ($user->hasRole('delivery_boy')) {

                $data[] = $this->get_seller_info($user);
            }
        }
        $user_arr = [
            'data' => $data,
            'status' => true,
            'message' => ''

        ];

        return response()->json($user_arr, 200);
    }


    public function get_delivery_boy_info($delivery_boy_info)
    {
        $user = User::find($delivery_boy_info);
        if (!$user)
            return null;
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
        $user_arr = [
            'data' => $data_info,
            'status' => true,
            'message' => ''

        ];

        return response()->json($user_arr, 200);

//        return $data_info;
    }


}
