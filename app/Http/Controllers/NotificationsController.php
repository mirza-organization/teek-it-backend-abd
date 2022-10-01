<?php

namespace App\Http\Controllers;

use App\DeviceToken;
use App\notifications;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Throwable;

class NotificationsController extends Controller
{
    // /**
    //  * Create a new controller instance.
    //  *
    //  * @return void
    //  */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    //    /**
    ////     * Display a listing of the resource.
    ////     *
    ////     * @return \Illuminate\Http\Response
    ////     */
    ////    public function index()
    ////    {
    ////        //
    ////    }
    ////
    ////    /**
    ////     * Show the form for creating a new resource.
    ////     *
    ////     * @return \Illuminate\Http\Response
    ////     */
    ////    public function create()
    ////    {
    ////        //
    ////    }
    ////
    ////    /**
    ////     * Store a newly created resource in storage.
    ////     *
    ////     * @param  \Illuminate\Http\Request  $request
    ////     * @return \Illuminate\Http\Response
    ////     */
    ////    public function store(Request $request)
    ////    {
    ////        //
    ////    }
    ////
    ////    /**
    ////     * Display the specified resource.
    ////     *
    ////     * @param  \App\notifications  $notifications
    ////     * @return \Illuminate\Http\Response
    ////     */
    ////    public function show(notifications $notifications)
    ////    {
    ////        //
    ////    }
    ////
    ////    /**
    ////     * Show the form for editing the specified resource.
    ////     *
    ////     * @param  \App\notifications  $notifications
    ////     * @return \Illuminate\Http\Response
    ////     */
    ////    public function edit(notifications $notifications)
    ////    {
    ////        //
    ////    }
    ////
    ////    /**
    ////     * Update the specified resource in storage.
    ////     *
    ////     * @param  \Illuminate\Http\Request  $request
    ////     * @param  \App\notifications  $notifications
    ////     * @return \Illuminate\Http\Response
    ////     */
    ////    public function update(Request $request, notifications $notifications)
    ////    {
    ////        //
    ////    }
    ////
    ////    /**
    ////     * Remove the specified resource from storage.
    ////     *
    ////     * @param  \App\notifications  $notifications
    ////     * @return \Illuminate\Http\Response
    ////     */
    ////    public function destroy(notifications $notifications)
    ////    {
    ////        //
    ////    }
    ///
    ///

    public function getNotifications()
    {
        $notifications = notifications::query()->where('user_id', '=', Auth::id())->get();
        if ($notifications->count() <= 0) {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => 'No New Notifications'
            ], 200);
        } else {
            return response()->json([
                'data' => $notifications,
                'status' => true,
                'message' => 'User Notifications'
            ], 200);
        }
    }

    public function deleteNotification($notification_id)
    {
        try {
            $notification = notifications::find($notification_id);
            if (!empty($notification)) {
                $notification->delete();
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.ITEM_DELETED'),
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
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

    // public function send_notification(Request $request)
    // {
    //     $validate = notifications::validator($request);
    //     if ($validate->fails()) {
    //         return response()->json([
    //             'data' => $validate->messages(),
    //             'status' => false,
    //             'message' => 'Validation error'
    //         ], 400);
    //     }
    //     $sender_id = Auth::id();
    //     $notification = new notifications();
    //     $notification->sender_id = $sender_id;
    //     $notification->user_id = $request->user_id;
    //     $notification->title  = $request->title;
    //     $notification->message = $request->message;
    //     $notification->other_data = $request->other_data;
    //     $notification->save();
    //     return response()->json([
    //         'data' => [],
    //         'status' => true,
    //         'message' => 'Notification Sent'
    //     ], 200);
    // }
    /**
     * Returns notification form view
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function notificationHome(Request $request)
    {
        if (Auth::user()->hasRole('superadmin')) {
            return view('admin.notification');
        } else {
            abort(404);
        }
    }
    /**
     * It will send notifications
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function notificationSend(Request $request)
    {
        try {
            if (Auth::user()->hasRole('superadmin')) {
                $validatedData = notifications::validator($request);
                if ($validatedData->fails()) {
                    flash('Error in sending notification because a required field is missing or invalid data.')->error();
                    return Redirect::back()->withInput($request->input());
                }

                $firebaseToken = DeviceToken::whereNotNull('device_token')->pluck('device_token')->all();
                $data = [
                    "registration_ids" => $firebaseToken,
                    "data" => [
                        "title" => $request->title,
                        "message" => $request->message,
                    ],
                    "priority" => "high"
                ];
                $dataString = json_encode($data);
                $headers = [
                    'Authorization: key=AAAAQ4iVuPM:APA91bGUp791v4RmZlEm3Dge71Yoj_dKq-XIytfnHtvCnHdmiH-BTZGlaCHGydnWvd976Mm5bSU6OFUNZqSf9YdamZifR3HMUl4m57RE21vSzrgGpfHmvYS47RQxDHV4WIN4zPFfNO-A',
                    'Content-Type: application/json',
                ];
                /**
                 * For Android devices
                 */
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
                curl_exec($ch);
                /**
                 * For iOS devices
                 */
                $dataForiOS = [
                    "registration_ids" => $firebaseToken,
                    "data" => [
                        "title" => $request->title,
                        "body" => $request->message,
                    ],
                    "priority" => 10
                ];
                $dataString = json_encode($dataForiOS);
                $headers = [
                    'Authorization: key=AAAAQ4iVuPM:APA91bGUp791v4RmZlEm3Dge71Yoj_dKq-XIytfnHtvCnHdmiH-BTZGlaCHGydnWvd976Mm5bSU6OFUNZqSf9YdamZifR3HMUl4m57RE21vSzrgGpfHmvYS47RQxDHV4WIN4zPFfNO-A',
                    'Content-Type: application/json',
                ];
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_HTTP09_ALLOWED, true);
                curl_setopt_array($curl, array(
                    CURLOPT_PORT => "443",
                    CURLOPT_URL => "https://api.push.apple.com:443/3/device/" . $firebaseToken . "",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $dataString,
                    CURLOPT_HTTPHEADER => $headers
                ));
                // CURLOPT_HTTPHEADER => array(
                //     "apns-topic: com.teekit.customer", // put it here your aplication bundle id
                //     "authorization: bearer " . $firebaseToken . "",
                // )
                print_r($curl);
                exit;
                curl_close($ch);
                curl_close($curl);
                return back()->with('success', 'Notification send successfully.');
            } else {
                abort(404);
            }
        } catch (Throwable $error) {
            report($error);
            return back()->with('error', 'Failed to send the notification due to some internal error.');
        }
    }
    /**
     * Test send notifications firebase API
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function notificationSendTest(Request $request)
    {
        try {
            $validatedData = notifications::validator($request);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => $validatedData->errors(),
                    'status' => true,
                    'message' => ""
                ], 422);
            }

            $firebaseToken = DeviceToken::whereNotNull('device_token')->pluck('device_token')->all();
            // $data = [
            //     "registration_ids" => $firebaseToken,
            //     "data" => [
            //         "title" => $request->title,
            //         "message" => $request->message,
            //     ],
            //     "priority" => "high"
            // ];
            // $dataString = json_encode($data);
            // $headers = [
            //     'Authorization: key=AAAAQ4iVuPM:APA91bGUp791v4RmZlEm3Dge71Yoj_dKq-XIytfnHtvCnHdmiH-BTZGlaCHGydnWvd976Mm5bSU6OFUNZqSf9YdamZifR3HMUl4m57RE21vSzrgGpfHmvYS47RQxDHV4WIN4zPFfNO-A',
            //     'Content-Type: application/json',
            // ];

            // /**
            //  * For Android devices
            //  */
            // $ch = curl_init();
            // curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            // curl_setopt($ch, CURLOPT_POST, true);
            // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            // curl_exec($ch);
            /**
             * For iOS devices
             */
            // print_r( $firebaseToken); exit;
            $dataForiOS = [
                "registration_ids" => $firebaseToken,
                "notification" => [
                    "title" => $request->title,
                    "message" => $request->message,
                ],
                "priority" => 10
            ];
            $dataString = json_encode($dataForiOS);
            $headers = [
                'Authorization: key=AAAAQ4iVuPM:APA91bGUp791v4RmZlEm3Dge71Yoj_dKq-XIytfnHtvCnHdmiH-BTZGlaCHGydnWvd976Mm5bSU6OFUNZqSf9YdamZifR3HMUl4m57RE21vSzrgGpfHmvYS47RQxDHV4WIN4zPFfNO-A',
                'Content-Type: application/json',
            ];
            // print_r($firebaseToken[0]); exit;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTP09_ALLOWED, true);
            curl_setopt_array($curl, array(
                CURLOPT_PORT => "443",
                CURLOPT_URL => "https://api.push.apple.com:443/3/device/$firebaseToken[0]",
                // CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $dataString,
                CURLOPT_SSLCERT =>"apns.pem",
                CURLOPT_HTTPHEADER => array(
                    "apns-topic: com.teekit.customer", // put it here your aplication bundle id
                    "apns-push-type: alert",
                    "Authorization: key=AAAAQ4iVuPM:APA91bGUp791v4RmZlEm3Dge71Yoj_dKq-XIytfnHtvCnHdmiH-BTZGlaCHGydnWvd976Mm5bSU6OFUNZqSf9YdamZifR3HMUl4m57RE21vSzrgGpfHmvYS47RQxDHV4WIN4zPFfNO-A",
                    "Content-Type: application/json"
                )
            ));
            // CURLOPT_HTTPHEADER => array(
            //     "apns-topic: com.teekit.customer", // put it here your aplication bundle id
            //     "authorization: bearer " . $firebaseToken . "",
            // )
            $response = curl_exec($curl);
            // print_r($curl);
            // exit;
            // curl_close($ch);
            curl_close($curl);

            return response()->json([
                'data' => json_decode($response),
                'status' => true,
                'message' => ""
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
     * It will save/update device token of every user
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function saveToken(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'device_id' => 'required|string',
                'device_token' => 'required|string'
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validatedData->errors()
                ], 422);
            }
            $device_token = new DeviceToken();
            $count = $device_token::select()->where('device_id', $request->device_id)->count();
            if ($count == 0) {
                $device_token->user_id = $request->user_id;
                $device_token->device_id = $request->device_id;
                $device_token->device_token = $request->device_token;
                $device_token->save();
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.DATA_INSERTION_SUCCESS')
                ], 200);
            } else {
                $device_token::where('device_id', $request->device_id)
                    ->update(['user_id' => $request->user_id, 'device_token' => $request->device_token]);
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.DATA_UPDATED_SUCCESS')
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
}
