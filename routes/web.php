<?php

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

//Route::get('/', function () {
//    $routes = collect(\Route::getRoutes())->map(function ($route) { return $route->uri(); });
//    \Artisan::call('route:list', ['--sort'=>'method','--path' => 'api']);
//    $output = \Artisan::output();
//
//    return view('welcome',compact('output'));
//});

use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', 'HomeController@index')->name('home');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/inventory', 'HomeController@inventory')->name('inventory');
Route::get('/inventory/edit/{product_id}', 'HomeController@inventory_edit');
Route::get('/inventory/add', 'HomeController@inventory_add');
Route::post('/inventory/add', 'HomeController@inventory_adddb')->name('add_inventory');
Route::get('/inventory/image/delete/{image_id}', 'HomeController@delete_img');
Route::get('/settings/general', 'HomeController@general_settings');
Route::get('/settings/payment', 'HomeController@payment_settings');
Route::post('/settings/payment/update', 'HomeController@payment_settings_update')->name('payment_settings_update');
Route::post('/settings/user_img/update', 'HomeController@user_img_update')->name('user_img_update');
Route::post('/settings/time_update', 'HomeController@time_update')->name('time_update');
Route::post('/settings/location_update', 'HomeController@location_update')->name('location_update');
Route::post('/inventory/update/{product_id}', 'HomeController@inventory_update')->name('update_inventory');

Route::get('/exportProducts', 'ProductsController@exportProducts')->name('exportProducts');

Route::post('/importProducts', 'HomeController@importPordersroducts')->name('importProducts');

Route::get('/orders', 'HomeController@orders')->name('orders');
Route::get('/orders/ready_state/{order_id}', 'HomeController@change_order_status')->name('accept_order');





Route::get('/inventory/disable/{product_id}', 'HomeController@inventory_disable')->name('inventory_disable');
Route::get('/inventory/enable/{product_id}', 'HomeController@inventory_enable')->name('inventory_enable');





Route::get('/withdrawals', 'HomeController@withdrawals')->name('withdrawals');
Route::get('/withdrawals-drivers', 'HomeController@withdrawalDrivers')->name('withdrawals.drivers');
Route::post('/withdrawals', 'HomeController@withdrawals_request')->name('withdraw_request');
Route::get('/my_order_count', 'HomeController@my_order_count')->name('my_order_count');




Route::get('/users/{user_id}/status/{status}', 'HomeController@change_user_status')->name('change_user_status');

Route::post('/update_pages', 'HomeController@update_pages')->name('update_pages');


Route::get('/stores', 'HomeController@admin_stores');
Route::get('/customers', 'HomeController@admin_customers');
Route::get('/drivers', 'HomeController@admin_drivers');
Route::get('/aorders', 'HomeController@admin_orders');
Route::get('/asetting', 'HomeController@asetting');
Route::get('/acategories', 'HomeController@all_cat');
Route::post('/acategories/{id}/update', 'HomeController@update_cat')->name('update_cat');
Route::post('/acategories/add_cat', 'HomeController@add_cat')->name('add_cat');
Route::get('/queries', 'HomeController@admin_queries');


Route::get('auth/verify', 'Auth\AuthController@verify');
Route::group(['middleware' => ['role:superadmin'],'prefix' => 'admin','namespace'=>'Admin'], function($router)
{

//    Route::get('/test', 'HomeController@index')->name('home');

});

Route::get('/customer/{user_id}/details', 'HomeController@admin_customer_details')->name('customer_details');
