<?php

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\QtyController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\Api\v1\DriverController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PromoCodesController;
use App\Http\Controllers\RattingsController;
use App\Http\Controllers\WithdrawalRequestsController;
use Illuminate\Support\Facades\Route;
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

Route::get('/', function () {
    return 'Teek it API Routes Are Working Fine :)';
});
/*
|--------------------------------------------------------------------------
| Registration, confirmations and verification
|--------------------------------------------------------------------------
*/
Route::post('password/email', [ForgotPasswordController::class, 'getResetToken']);
Route::post('password/reset', [ResetPasswordController::class, 'reset']);
Route::post('auth/register', [AuthController::class, 'register']);
Route::get('auth/verify', [AuthController::class, 'verify']);
Route::post('auth/register_google', [AuthController::class, 'registerGoogle']);
Route::post('auth/login_google', [AuthController::class, 'loginGoogle']);
/*
|--------------------------------------------------------------------------
| Authentication API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('update', [AuthController::class, 'updateUser']);
    Route::post('updateStatus', [AuthController::class, 'updateStatus']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('delivery_boys', [AuthController::class, 'deliveryBoys']);
    Route::get('get_user/{user_id}', [AuthController::class, 'getDeliveryBoyInfo']);
    Route::post('user/delete', [AuthController::class, 'deleteUser']);
});
/*
|--------------------------------------------------------------------------
| Qty API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('qty')->group(function () {
    Route::get('all', [QtyController::class, 'all']);
    Route::get('product/{store_id}', [QtyController::class, 'getByStoreId']);
    Route::get('product/{store_id}/{prod_id}', [QtyController::class, 'getById']);
    Route::post('update/{prod_id}', [QtyController::class, 'updateById']);
    // Route::post('insert_parent_qty_to_child', 'QtyController@insertParentQtyToChild');
    // Route::get('multi-curl', 'QtyController@multiCURL');
    // Route::get('shifting-qty', 'QtyController@shiftQtyInProductsToQtyTable');
});
/*
|--------------------------------------------------------------------------
| Category API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('category')->group(function () {
    Route::post('add', [CategoriesController::class, 'add']);
    Route::post('update/{product_id}', [CategoriesController::class, 'update']);
    Route::get('all', [CategoriesController::class, 'all']);
    Route::get('view/{category_id}', [CategoriesController::class, 'products']);
    Route::get('get-stores-by-category/{category_id}', [CategoriesController::class, 'stores']);
});
/*
|--------------------------------------------------------------------------
| Page API Routes
|--------------------------------------------------------------------------
*/
Route::get('page', [PagesController::class, 'getPage']);
/*
|--------------------------------------------------------------------------
| Seller API Routes
|--------------------------------------------------------------------------
*/
Route::get('sellers', [UsersController::class, 'sellers']);
Route::get('sellers/{seller_id}/{product_name}', [AuthController::class, 'searchSellerProducts']);
/*
|--------------------------------------------------------------------------
| Products API Routes Without JWT Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('product')->group(function () {
    Route::post('search', [ProductsController::class, 'search']);
    Route::get('all', [ProductsController::class, 'all']);
    Route::get('view/{product_id}', [ProductsController::class, 'view']);
    Route::post('view/bulk', [ProductsController::class, 'bulkView']);
    Route::get('seller/{seller_id}', [ProductsController::class, 'sellerProducts']);
    Route::get('sortbyprice', [ProductsController::class, 'sortByPrice']);
    Route::get('sortByLocation', [ProductsController::class, 'sortByLocation']);
    Route::post('recheck_products', [OrdersController::class, 'recheckProducts']);
    Route::get('featured/{store_id}', [ProductsController::class, 'featuredProducts']);
    Route::get('drop-qty-column', [ProductsController::class, 'dropProductsTableQtyColumn']);
});
/*
|--------------------------------------------------------------------------
| Driver API Routes Without JWT Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('driver')->group(function () {
    Route::post('/register', [DriverController::class, 'registerDriver']);
    Route::post('/login', [DriverController::class, 'loginDriver']);
});
/*
|--------------------------------------------------------------------------
| Notifications API Routes Without JWT Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('notifications')->group(function () {
    Route::get('', [NotificationsController::class, 'getNotifications']);
    Route::post('save_token', [NotificationsController::class, 'saveToken']);
    Route::get('delete/{notification_id}', [NotificationsController::class, 'deleteNotification']);
    Route::post('send_test', [NotificationsController::class, 'notificationSendTest']);
});
/*
|--------------------------------------------------------------------------
| API Routes With JWT Authentication
|--------------------------------------------------------------------------
*/
Route::middleware(['jwt.verify'])->group(function () {
    Route::prefix('product')->group(function () {
        Route::post('add', [ProductsController::class, 'add']);
        Route::post('add/bulk', [ProductsController::class, 'importProductsAPI']);
        Route::post('update/{product_id}', [ProductsController::class, 'update']);
        Route::post('update_price_qty/bulk', [ProductsController::class, 'updatePriceAndQtyBulk']);
        Route::get('delete/{product_id}', [ProductsController::class, 'delete']);
        Route::get('delete_image/{image_id}/{product_id}', [ProductsController::class, 'deleteImage']);
        Route::post('ratings/add', [RattingsController::class, 'add']);
        Route::post('ratings/update', [RattingsController::class, 'update']);
        Route::get('ratings/delete/{ratting_id}', [RattingsController::class, 'delete']);
    });

    Route::prefix('withdrawal')->group(function () {
        Route::get('getRequests', [WithdrawalRequestsController::class, 'getRequests']);
        Route::post('sendRequest', [WithdrawalRequestsController::class, 'sendRequest']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('', [OrdersController::class, 'index']);
        Route::post('new', [OrdersController::class, 'new']);
        Route::get('seller', [OrdersController::class, 'sellerOrders']);
        Route::get('delivery_boy_orders/{delivery_boy_id}', [OrdersController::class, 'deliveryBoyOrders']);
        Route::get('assign_order', [OrdersController::class, 'assignOrder']);
        Route::get('cancel_order', [OrdersController::class, 'cancelOrder']);
        Route::get('update_assign', [OrdersController::class, 'updateAssign']);
        Route::post('customer_cancel_order', [OrdersController::class, 'customerCancelOrder']);
        Route::post('update', [OrdersController::class, 'updateOrder']);
        Route::post('/estimated-time/{id}', [OrdersController::class, 'storeEstimatedTime']);
        Route::get('/get-order-details/{id}', [OrdersController::class, 'getOrderDetails']);
        Route::get('/recent_orders/{store_id}', [OrdersController::class, 'recentOrders']);
    });

    Route::prefix('driver')->group(function () {
        Route::get('/info/{id}', [DriverController::class, 'info']);
        Route::post('/add-lat-lon', [DriverController::class, 'addLatLon']);
        Route::get('/withdrawable-balance', [DriverController::class, 'getWithdrawalBalance']);
        Route::get('/request-withdrawal-balance', [DriverController::class, 'submitWithdrawal']);
        Route::post('/bank-details', [DriverController::class, 'submitBankAccountDetails']);
        Route::get('/all-withdrawals', [DriverController::class, 'driverAllWithdrawalRequests']);
        Route::post('/check_verification_code/{order_id}', [DriverController::class, 'checkVerificationCode']);
        Route::post('/driver_failed_to_enter_code/{order_id}', [DriverController::class, 'driverFailedToEnterCode']);
    });

    Route::prefix('promocodes')->group(function () {
        Route::post('/validate', [PromoCodesController::class, 'promocodesValidate']);
        Route::post('/fetch_promocode_info', [PromoCodesController::class, 'fetchPromocodeInfo']);
        Route::get('/all', [PromoCodesController::class, 'allPromocodes']);
    });

    Route::get('keys', [AuthController::class, 'keys']);
});
/*
|--------------------------------------------------------------------------
| Random API Routes
|--------------------------------------------------------------------------
*/
Route::get('payment_intent', function () {
    $ch = curl_init();
    $amount = $_REQUEST['amount'];
    $currency = $_REQUEST['currency'];
    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "amount=$amount&currency=$currency&metadata[integration_check]=accept_a_payment");
    curl_setopt($ch, CURLOPT_USERPWD, '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' . ':' . '');

    $headers = array();
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    return response()->json(json_decode($result), 200);
});

Route::get('payment_intent/test', function () {
    $ch = curl_init();
    $amount = $_REQUEST['amount'];
    $currency = $_REQUEST['currency'];
    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "amount=$amount&currency=$currency&metadata[integration_check]=accept_a_payment");
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
});

Route::get('time', function () {
    return response()->json([
        'data' => time(),
        'status' => true,
        'message' => ''
    ], 200);
});

Route::get('generate_hash', function () {
    return response()->json([
        'data' => Hash::make($_REQUEST['password']),
        'status' => true,
        'message' => ''
    ], 200);
});

Route::fallback(function () {
    return response()->json([
        'data' => [],
        'status' => false,
        'message' => 'API Not Found.'
    ], 404);
});
