<?php

namespace App\Http\Controllers;

use App\notifications;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
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

    public function get_notifications(){
        $notifications = notifications::query()->where('user_id','=',Auth::id())->get();
        $response = array(
            'data' => $notifications,
            'status' => true,
            'message' => 'User Notifications'
        );
        if ($notifications->count()<=0){
            $response = array(
                'data' => [],
                'status' => false,
                'message' => 'No New Notifications'
            );
        }
        return response()->json($response, 200);
    }

    public function delete_notification($notification_id){
        $notification = notifications::find($notification_id);
        $response = array(
            'data' => [],
            'status' => false,
            'message' => 'There is No Notification with the given ID or maybe already Deleted'
        );
        if (!empty($notification)){
            $notification->delete();
            $response = array(
                'data' => [],
                'status' => true,
                'message' => 'Notification Deleted'
            );
        }
        return response()->json($response, 200);
    }
    
    public function send_notification(Request $request){
        $validate = notifications::validator($request);
        if ($validate->fails()) {
            $response = array(
                'data' => $validate->messages(),
                'status' => false,
                'message' => 'Validation error'
            );
            return response()->json($response, 400);
        }
        $sender_id=Auth::id();
        $notification = new notifications();
        $notification->sender_id = $sender_id;
        $notification->user_id = $request->user_id;
        $notification->title  = $request->title;
        $notification->message = $request->message;
        $notification->other_data = $request->other_data;
        $notification->save();
        $response = array(
            'data' => [],
            'status' => true,
            'message' => 'Notification Sent'
        );
        return response()->json($response, 200);
    }
}
