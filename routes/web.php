<?php

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/testing', function () {
   return response("Hello World!");
});
Route::get('send-message', function () {
    $receiverNumber = "+923006694349";
    $message = "This is testing from teek-it";
    try {
        $account_sid = config("app.TWILIO_SID");
        $auth_token = config("app.TWILIO_TOKEN");
        $twilio_number = config("app.TWILIO_FROM");

        $client = new Client($account_sid, $auth_token);
        $client->messages->create($receiverNumber, [
            'from' => $twilio_number,
            'body' => $message
        ]);
        dd('SMS Sent Successfully.');
    } catch (Exception $e) {
        dd("Error: " . $e->getMessage());
    }
});

/*
|--------------------------------------------------------------------------
| For Adding Authentication On All Of The Following Routes
|--------------------------------------------------------------------------
*/
Auth::routes();
/*
|--------------------------------------------------------------------------
| Home Routes
|--------------------------------------------------------------------------
*/
Route::get('/', 'HomeController@index')->name('home');
/*
|--------------------------------------------------------------------------
| Inventory Routes
|--------------------------------------------------------------------------
*/
Route::get('/inventory', 'HomeController@inventory')->name('inventory');
Route::get('/inventory/edit/{product_id}', 'HomeController@inventory_edit');
Route::get('/inventory/add', 'HomeController@inventory_add');
Route::get('/inventory/add_bulk', 'HomeController@inventory_add_bulk');
Route::post('/inventory/add', 'HomeController@inventory_add_db')->name('add_inventory');
Route::get('/inventory/image/delete/{image_id}', 'HomeController@delete_img');
Route::post('/inventory/update/{product_id}', 'HomeController@inventory_update')->name('update_inventory');
Route::get('/inventory/disable/{product_id}', 'HomeController@inventory_disable')->name('inventory_disable');
Route::get('/inventory/enable/{product_id}', 'HomeController@inventory_enable')->name('inventory_enable');
Route::get('/inventory/enable_all', 'HomeController@inventory_enable_all')->name('enable_all');
Route::get('/inventory/disable_all', 'HomeController@inventory_disable_all')->name('disable_all');
/*
|--------------------------------------------------------------------------
| Settings Routes
|--------------------------------------------------------------------------
*/
Route::get('/settings/general', 'HomeController@general_settings');
Route::get('/settings/payment', 'HomeController@payment_settings');
Route::post('/settings/payment/update', 'HomeController@payment_settings_update')->name('payment_settings_update');
Route::post('/settings/user_img/update', 'HomeController@user_img_update')->name('user_img_update');
Route::post('/settings/time_update', 'HomeController@time_update')->name('time_update');
Route::post('/settings/location_update', 'HomeController@location_update')->name('location_update');
Route::get('/settings/change_settings/{setting_name}/{value}', 'HomeController@change_settings')->name('change_settings')->where(['setting_name' => '^[a-z_]*$', 'value' => '[0-9]+']);
/*
|--------------------------------------------------------------------------
| Imp/Exp Products Routes
|--------------------------------------------------------------------------
*/
Route::get('/exportProducts', 'ProductsController@exportProducts')->name('exportProducts');
Route::post('/importProducts', 'HomeController@importProducts')->name('importProducts');
/*
|--------------------------------------------------------------------------
| Orders Routes
|--------------------------------------------------------------------------
*/
Route::get('/orders', 'HomeController@orders')->name('orders');
Route::get('/orders/ready_state/{order_id}', 'HomeController@change_order_status')->name('accept_order');
Route::get('/orders/cancel/{order_id}', 'HomeController@cancel_order')->name('cancel_order');
/*
|--------------------------------------------------------------------------
| Withdrawal Routes
|--------------------------------------------------------------------------
*/
Route::get('/withdrawals', 'HomeController@withdrawals')->name('withdrawals');
Route::get('/withdrawals-drivers', 'HomeController@withdrawalDrivers')->name('withdrawals.drivers');
Route::post('/withdrawals', 'HomeController@withdrawals_request')->name('withdraw_request');

Route::get('auth/verify', 'Auth\AuthController@verify');
Route::group(['middleware' => ['role:superadmin'], 'prefix' => 'admin', 'namespace' => 'Admin'], function ($router) {
    //Route::get('/test', 'HomeController@index')->name('home');
});
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::get('/stores', 'HomeController@admin_stores');
Route::get('/customers', 'HomeController@admin_customers');
Route::get('/drivers', 'HomeController@admin_drivers');
Route::get('/aorders', 'HomeController@admin_orders');
Route::get('/complete-orders', 'HomeController@complete_orders')->name('complete.order');
Route::get('/mark-complete-order/{id}', 'HomeController@mark_complete_order')->name('mark.complete.order');
Route::get('/asetting', 'HomeController@asetting');
Route::get('/acategories', 'HomeController@all_cat');
Route::post('/acategories/{id}/update', 'HomeController@update_cat')->name('update_cat');
Route::post('/acategories/add_cat', 'HomeController@add_cat')->name('add_cat');
Route::get('/acategories/delete_cat/{id}', 'HomeController@delete_cat')->name('delete_cat');
Route::get('/queries', 'HomeController@admin_queries');
Route::get('/customer/{user_id}/details', 'HomeController@admin_customer_details')->name('customer_details');
Route::get('/store/application-fee/{user_id}/{application_fee}', 'Admin\UserAndRoleController@updateApplicationFee')->name('application_fee');
Route::post('/update_pages', 'HomeController@update_pages')->name('update_pages');
Route::get('/users/{user_id}/status/{status}', 'HomeController@change_user_status')->name('change_user_status');
/*
|--------------------------------------------------------------------------
| Total Orders Count Route
|--------------------------------------------------------------------------
*/
Route::get('/my_order_count', 'HomeController@my_order_count')->name('my_order_count');
