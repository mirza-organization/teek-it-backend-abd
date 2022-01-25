<?php

namespace App\Http\Controllers\Auth;

use App\Mail\StoreRegisterMail;
use App\Models\Role;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Laracasts\Flash\Flash;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
//        print_r($data);die;
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
//            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
//            'address' => 'required|string|max:255',
//            'city' => 'required|string|max:255',
//            'postcode' => 'required|string|max:255',
//            'phone_number' => 'required|string|max:255',
//            'age' => 'required|string|max:255',
//            'gender' => 'required|string|max:255',
//            'driving_lesson_cost' => 'string|max:255',
//            'approved_driving_instructor' => 'boolean',
//            'years_of_experience' => 'string',
//            'role' => 'required|string|max:255',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return User|\Illuminate\Http\RedirectResponse
     */
    protected function register(Request $request)
    {
        $is_valid = $this->validator($request->all());
        if ($is_valid->fails()) {
            \flash('Email Already Exists')->error();
            return Redirect::back()->withInput($request->input())
                ->withErrors(['name.required', 'Name is required']);
        }
        $data = $request->toArray();

        $role = Role::where('name', 'seller')->first();

        $User = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'business_name' => $data['company_name'],
            'business_phone' => $data['company_phone'],
            'is_active' => 0,
        ]);

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

        $subject = env('APP_NAME') . ': Account Verification';
        Mail::to($User->email)
            ->send(new StoreRegisterMail($html, $subject));

        Flash::message('We have Sent you an Email to Verify your Account');

        $admin_users = Role::with('users')->where('name', 'superadmin')->first();
        $store_link = $FRONTEND_URL . '/customer/' . $User->id . '/details';
        $admin_subject = env('APP_NAME') . ': New Store Register';
        foreach ($admin_users->users as $user) {
            $adminHtml = '<html>
            Hi, ' . $user->name . '<br><br>

            A new store has been register to your site  ' . env('APP_NAME') . '.

<br>
            Please click on below link to activate store. <br><br>
            <a href="' . $store_link . '">Verify</a> OR Copy This in your Browser
            ' . $store_link . '
<br><br><br>
        </html>';
            if (!empty($adminHtml)) Mail::to($user->email)
                ->send(new StoreRegisterMail($adminHtml, $admin_subject));
        }
        return Redirect::route('login');
    }
//    protected function registered(Request $request, $user)
//    {
//        Flash::message('You have successfully verified your account.');
////
//        return Redirect::route('login');
//        //
//    }
}
