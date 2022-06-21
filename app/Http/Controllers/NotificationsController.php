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
        $notification = notifications::find($notification_id);
        if (!empty($notification)) {
            $notification->delete();
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => 'Notification Deleted'
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => 'There is No Notification with the given ID or maybe already Deleted'
            ], 200);
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
     * Returns notification form
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function homeNotification(Request $request)
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
     * @version 1.0.0
     */
    public function sendNotification(Request $request)
    {
        if (Auth::user()->hasRole('superadmin')) {
            $validatedData = notifications::validator($request);
            if ($validatedData->fails()) {
                flash('Error in sending notification because a required field is missing or invalid data.')->error();
                return Redirect::back()->withInput($request->input());
            }

            $firebaseToken = DeviceToken::whereNotNull('device_token')->pluck('device_token')->all();
            $SERVER_API_KEY = env('FCM_SERVER_KEY');
            $data = [
                "registration_ids" => $firebaseToken,
                "notification" => [
                    "title" => $request->title,
                    "body" => $request->body,
                ]
            ];
            $dataString = json_encode($data);
            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            $response = curl_exec($ch);

            return back()->with('success', 'Notification send successfully.');
        } else {
            abort(404);
        }
    }
    /**
     * It will save device token of every user
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function saveToken(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'device_token' => 'required|string'
        ]);
        if ($validatedData->fails()) {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $validatedData->errors()
            ], 422);
        }
        try {
            $device_toke = new DeviceToken();
            $device_toke->user_id = $request->user_id;
            $device_toke->device_token = $request->device_token;
            $device_toke->save();
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => config('constants.DATA_INSERTION_SUCCESS')
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 200);
        }
    }
}
