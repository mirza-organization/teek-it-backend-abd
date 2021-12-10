<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function (Request $request) {
    return 'Working';
});

// Registration, confirmations and verification
Route::post('password/email', 'Auth\ForgotPasswordController@getResetToken');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
Route::post('auth/register', 'Auth\AuthController@register');
Route::get('auth/verify', 'Auth\AuthController@verify');

// Authentication Routes
Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('login', 'Auth\AuthController@login');
    Route::post('change-password', 'Auth\AuthController@changePassword');
    Route::post('logout', 'Auth\AuthController@logout');
    Route::post('refresh', 'Auth\AuthController@refresh');
    Route::post('update', 'Auth\AuthController@updateUser');
    Route::post('updateStatus', 'Auth\AuthController@updateStatus');
    Route::get('me', 'Auth\AuthController@me');
    Route::get('delivery_boys', 'Auth\AuthController@delivery_boys');
    Route::get('get_user/{user_id}', 'Auth\AuthController@get_delivery_boy_info');
});

Route::group(['prefix' => 'category'], function ($router) {
    //        Route::post('add', 'CategoriesController@add');
    //        Route::post('update/{product_id}', 'CategoriesController@update');
    Route::get('all', 'CategoriesController@all');
    Route::get('view/{category_id}', 'CategoriesController@Products');
    Route::get('get-stores-by-category/{category_id}', 'CategoriesController@stores');
});

Route::group(['prefix' => 'product'], function ($router) {
    Route::post('search', 'ProductsController@search');
    Route::get('all', 'ProductsController@all');
});

Route::group(['prefix' => 'page'], function ($router) {
    Route::get('', 'PagesController@get_page');
});
Route::get('sellers', 'Auth\AuthController@sellers');
Route::get('sellers/{seller_id}', 'Auth\AuthController@seller_products');

Route::group(['middleware' => ['jwt.verify']], function ($router) {
    Route::group(['prefix' => 'product'], function ($router) {
        Route::post('add', 'ProductsController@add');
        
        Route::get('view/{product_id}', 'ProductsController@view');
        Route::post('view/bulk', 'ProductsController@bulkView');
        //        Route::get('edit/{id}', 'ProductsController@edit');
        Route::post('update/{product_id}', 'ProductsController@update');
        Route::get('delete/{product_id}', 'ProductsController@delete');
        Route::get('delete_image/{image_id}/{product_id}', 'ProductsController@delete_image');

        Route::get('sortbyprice', 'ProductsController@sortByPrice');
        Route::get('sortByLocation', 'ProductsController@sortByLocation');

        Route::post('ratings/add', 'RattingsController@add');
        Route::post('ratings/update', 'RattingsController@update');
        Route::get('ratings/delete/{ratting_id}', 'RattingsController@delete');
    });

    Route::group(['prefix' => 'withdrawal'], function ($router) {
        Route::get('getRequests', 'WithdrawalRequestsController@getRequests');
        Route::post('sendRequest', 'WithdrawalRequestsController@sendRequest');
    });

    Route::group(['prefix' => 'orders'], function ($router) {
        Route::get('', 'OrdersController@index');
        Route::get('seller', 'OrdersController@seller_orders');
        Route::get('delivery_boy_orders/{delivery_boy_id}', 'OrdersController@delivery_boy_orders');
        Route::get('assign_order', 'OrdersController@assign_order');
        Route::get('update_assign', 'OrdersController@update_assign');
        Route::post('new', 'OrdersController@new');
        Route::post('update', 'OrdersController@updateOrder');
        Route::post('/estimated-time/{id}', 'Api\v1\OrderController@storeEstimatedTime');
        Route::get('/get-order-details/{id}', 'Api\v1\OrderController@getOrderDetails');
    });

    Route::group(['prefix' => 'notifications'], function ($router) {
        Route::get('', 'NotificationsController@get_notifications');
        Route::get('delete/{notification_id}', 'NotificationsController@delete_notification');
        Route::post('send', 'NotificationsController@send_notification');
    });

    Route::group(['prefix' => 'driver'], function () {
        Route::get('/info/{id}', 'Api\v1\DriverController@info');
        Route::post('/add-lat-lng', 'Api\v1\DriverController@addLatLng');
        Route::get('/withdrawable-balance', 'Api\v1\DriverController@getWithdrawalBalance');
        Route::get('/request-withdrawal-balance', 'Api\v1\DriverController@submitWithdrawal');
        Route::post('/bank-details', 'Api\v1\DriverController@submitBankAccountDetails');
        Route::get('/all-withdrawals', 'Api\v1\DriverController@driverAllWithdrawalRequests');
    });
});

// Superadmin Routes
//Route::group(['middleware' => ['jwt.verify','role:superadmin'],'prefix' => 'sadmin','namespace'=>'Admin'], function($router)
//{
//
//    // User Routes
//    Route::post('user','UserAndRoleController@createUser');
//    Route::post('user/update', 'UserAndRoleController@updateUser');
//    Route::get('users', 'UserAndRoleController@getUsers');
//    Route::post('users/dt', 'UserAndRoleController@getDTUsers');
//    Route::get('user/{id}', 'UserAndRoleController@getUser');
//    Route::delete('user/{id}/delete', 'UserAndRoleController@deleteUser');
//    Route::get('user/{id}/reset-password', 'UserAndRoleController@resetPassword');
//
//    Route::post('user/role/assign', 'UserAndRoleController@assignRole');
//    Route::get('user/role/{id}', 'UserAndRoleController@getUserRole');
//
//    // Role Routes
//    Route::post('role', 'UserAndRoleController@createRole');
//    Route::post('role/update', 'UserAndRoleController@updateRole');
//    Route::get('roles', 'UserAndRoleController@getRoles');
//    Route::post('roles/dt', 'UserAndRoleController@getDTRoles');
//    Route::get('role/{id}', 'UserAndRoleController@getRole');
//    Route::delete('role/{id}/delete', 'UserAndRoleController@deleteRole');
//
//});

Route::get('payment_intent', function () {
    $ch = curl_init();
    $amount = $_REQUEST['amount'];
    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "amount=$amount&currency=eur&metadata[integration_check]=accept_a_payment");
    curl_setopt($ch, CURLOPT_USERPWD, 'sk_test_51IY9sYIiDDGv1gaVKsxU0EXr96lHcCvwXHwYAdN81Cqrj1TBL4HErJpczWJpYFIQ1qbCOQxnxIM3UfsBtWC2MKeD00QRkUKg6q' . ':' . '');

    $headers = array();
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    return response()->json(json_decode($result), 200);
    // json_decode($result);
});

Route::get('time', function () {
    return response()->json([
        'data' => time(),
        'status' => true,
        'message' => ''
    ], 200);
});

Route::fallback(function () {
    return response()->json([
        'status' => false,
        'message' => 'Page Not Found.'
    ], 404);
});
