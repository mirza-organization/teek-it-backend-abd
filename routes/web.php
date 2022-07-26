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
Route::get('/inventory/edit/{product_id}', 'HomeController@inventoryEdit');
Route::get('/inventory/add', 'HomeController@inventoryAdd');
Route::get('/inventory/add_bulk', 'HomeController@inventoryAddBulk');
Route::post('/inventory/add', 'HomeController@inventoryAddDB')->name('add_inventory');
Route::get('/inventory/image/delete/{image_id}', 'HomeController@deleteImg');
Route::post('/inventory/update/{product_id}', 'HomeController@inventoryUpdate')->name('update_inventory');
Route::get('/inventory/disable/{product_id}', 'HomeController@inventoryDisable')->name('inventory_disable');
Route::get('/inventory/enable/{product_id}', 'HomeController@inventoryEnable')->name('inventory_enable');
Route::get('/inventory/enable_all', 'HomeController@inventoryEnableAll')->name('enable_all');
Route::get('/inventory/disable_all', 'HomeController@inventoryDisableAll')->name('disable_all');
Route::get('/inventory/feature/add/{product_id}', 'HomeController@markAsFeatured')->name('markAsFeatured');
Route::get('/inventory/feature/remove/{product_id}', 'HomeController@removeFromFeatured')->name('removeFromFeatured');
/*
|--------------------------------------------------------------------------
| User Settings Routes
|--------------------------------------------------------------------------
*/
Route::get('/settings/general', 'HomeController@generalSettings');
Route::get('/settings/payment', 'HomeController@paymentSettings');
Route::post('/settings/payment/update', 'HomeController@paymentSettingsUpdate')->name('payment_settings_update');
Route::post('/settings/user_img/update', 'HomeController@userImgUpdate')->name('user_img_update');
Route::post('/settings/time_update', 'HomeController@timeUpdate')->name('time_update');
Route::post('/settings/location_update', 'HomeController@locationUpdate')->name('location_update');
Route::post('/settings/password/update', 'HomeController@passwordUpdate')->name('password_update');
Route::get('/settings/change_settings/{setting_name}/{value}', 'HomeController@changeSettings')->name('change_settings')->where(['setting_name' => '^[a-z_]*$', 'value' => '[0-9]+']);
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
Route::get('/orders/ready_state/{order_id}', 'HomeController@changeOrderStatus')->name('accept_order');
Route::get('/orders/mark_as_delivered/{order_id}', 'HomeController@markAsDelivered')->name('mark_as_delivered');
Route::get('/orders/mark_as_completed/{order_id}', 'HomeController@markAsCompleted')->name('mark_as_completed');
Route::get('/orders/cancel/{order_id}', 'HomeController@cancelOrder')->name('cancel_order');
/*
|--------------------------------------------------------------------------
| Withdrawal Routes
|--------------------------------------------------------------------------
*/
Route::get('/withdrawals', 'HomeController@withdrawals')->name('withdrawals');
Route::get('/withdrawals-drivers', 'HomeController@withdrawalDrivers')->name('withdrawals.drivers');
Route::post('/withdrawals', 'HomeController@withdrawalsRequest')->name('withdraw_request');

Route::get('auth/verify', 'Auth\AuthController@verify');
Route::group(['middleware' => ['role:superadmin'], 'prefix' => 'admin', 'namespace' => 'Admin'], function ($router) {
    //Route::get('/test', 'HomeController@index')->name('home');
});
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::get('/notification/home', 'NotificationsController@notificationHome')->name('admin.notification.home');
Route::post('/notification/send', 'NotificationsController@notificationSend')->name('admin.notification.send');
Route::get('/stores', 'HomeController@adminStores');
Route::get('/customers', 'HomeController@adminCustomers');
Route::get('/drivers', 'HomeController@adminDrivers')->name('admin.drivers');
Route::get('/promocodes/home', 'PromoCodesController@promocodesHome')->name('admin.promocodes.home');
Route::post('/promocodes/add', 'PromoCodesController@promocodesAdd')->name('admin.promocodes.add');
Route::get('/promocodes/delete', 'PromoCodesController@promoCodesDel')->name('admin.promocodes.del');
Route::post('/promocodes/{id}/update', 'PromoCodesController@promoCodesUpdate')->name('admin.promocodes.update');
Route::get('/aorders', 'HomeController@adminOrders');
Route::get('/aorders/verified', 'HomeController@adminOrdersVerified');
Route::get('/aorders/unverified', 'HomeController@adminOrdersUnverified');
Route::get('/aorders/delete', 'HomeController@adminOrdersDel')->name('admin.del.orders');
Route::get('/complete-orders', 'HomeController@completeOrders')->name('complete.order');
Route::get('/mark-complete-order/{id}', 'HomeController@markCompleteOrder')->name('mark.complete.order');
Route::get('/asetting', 'HomeController@aSetting');
Route::get('/acategories', 'HomeController@allCat');
Route::post('/acategories/{id}/update', 'HomeController@updateCat')->name('update_cat');
Route::post('/acategories/add_cat', 'HomeController@addCat')->name('add_cat');
Route::get('/acategories/delete_cat/{id}', 'HomeController@deleteCat')->name('delete_cat');
Route::get('/queries', 'HomeController@adminQueries');
Route::get('/customer/{user_id}/details', 'HomeController@adminCustomerDetails')->name('customer_details');
Route::get('/store/application-fee/{user_id}/{application_fee}', 'Admin\UserAndRoleController@updateApplicationFee')->name('application_fee');
Route::post('/update_pages', 'HomeController@updatePages')->name('update_pages');
Route::get('/users/{user_id}/status/{status}', 'HomeController@changeUserStatus')->name('change_user_status');
Route::get('/users_del', 'HomeController@adminUsersDel')->name('admin.del.users');
/*
|--------------------------------------------------------------------------
| Total Orders Count Route
|--------------------------------------------------------------------------
*/
Route::get('/my_order_count', 'HomeController@myOrderCount')->name('my_order_count');

// Route::get('send-message', function () {
//     $receiverNumber = "+923006694349";
//     $message = "This is testing from teek-it";
//     try {
//         $account_sid = config("app.TWILIO_SID");
//         $auth_token = config("app.TWILIO_TOKEN");
//         $twilio_number = config("app.TWILIO_FROM");

//         $client = new Client($account_sid, $auth_token);
//         $client->messages->create($receiverNumber, [
//             'from' => $twilio_number,
//             'body' => $message
//         ]);
//         dd('SMS Sent Successfully.');
//     } catch (Exception $e) {
//         dd("Error: " . $e->getMessage());
//     }
// });