<?php

namespace App\Http\Controllers;

use App\Models\StuartDelivery;
use App\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use Throwable;

class StuartDeliveryController extends Controller
{
    /**
     * It will get a fresh token for hitting Stuart API's
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function stuartAccessToken()
    {
        $stuart_auth = Http::asForm()->post('https://api.sandbox.stuart.com/oauth/token', [
            'client_id' => config('app.STUART_SANDBOX_CLIENT_ID'),
            'client_secret' => config('app.STUART_SANDBOX_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
            'scope' => 'api'
        ]);
        $stuart_auth = $stuart_auth->json();
        return $stuart_auth['access_token'];
    }
    /**
     * Creates a stuart delivery job
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function stuartJobCreation(Request $request)
    {
        try {
            $order_details = Orders::query()->where('id', '=', $request->order_id)->first();
            // dd($order_details);
            $access_token = $this->stuartAccessToken();
            $job = [
                'job' => [
                    // 'pickup_at' => Carbon::now()->addMinutes(10),
                    'pickups' => [
                        [
                            'address' => '32 Coombe Ln, Raynes Park, London SW20 0LA',
                            // 'comment' => 'Ask Bobby',
                            'contact' => [
                                'firstname' => 'Bobby',
                                'lastname' => 'Brown',
                                'phone' => '+33610101010',
                                // 'email' => 'bobby.brown@pizzashop.com',
                                // 'company' => 'Pizza Shop'
                            ]
                        ]
                    ],
                    'dropoffs' => [
                        [
                            'package_type' => 'medium',
                            'package_description' => 'Package purchased from Teek it',
                            // 'client_reference' => '[your_client_ref]',
                            'address' => $order_details->address,
                            'comment' => 'Please try to call the customer before reaching the destination.',
                            'end_customer_time_window_start' => '2021-12-12T11:00:00.000+02:00',
                            'end_customer_time_window_end' => '2021-12-12T13:00:00.000+02:00',
                            'contact' => [
                                'firstname' => $order_details->receiver_name,
                                'lastname' => $order_details->receiver_name,
                                'phone' => $order_details->phone_number,
                                // 'email' => 'client3@email.com',
                                // 'company' => 'Sample Company Inc.'
                            ]
                        ]
                    ]
                ]
            ];
            $response = Http::withToken($access_token)->post('https://api.sandbox.stuart.com/v2/jobs', $job);
            $data = $response->json();
            // dd($data);
            $data = StuartDelivery::create([
                'order_id' => $request->order_id,
                'job_id' => $data['id']
            ]);
            if ($data) {
                Orders::where('id', $request->order_id)->update([
                    'order_status' => 'stuartDelivery'
                ]);
                // return response()->json(json_decode($response->getBody()->getContents()));
                flash('Stuart Delivery Has Been Initiated Successfully, You Can Please Check The Status By Clicking The "Check Status" Button')->success();
                return Redirect::back();
            }
        } catch (Throwable $error) {
            report($error);
            flash('An other delivery is in progress')->error();
            return Redirect::back();
            // return response()->json([
            //     'data' => [],
            //     'status' => false,
            //     'message' => $error
            // ], 500);
        }
    }

    /**
     * It will check the current status of a Stuart job
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function stuartJobStatus(Request $request)
    {
        try {
            $data = StuartDelivery::select('job_id')->where('order_id', $request->order_id)->first();
            $access_token = $this->stuartAccessToken();
            $response = Http::withToken($access_token)->patch('https://api.sandbox.stuart.com/v2/jobs/' . $data->job_id);
            $data = $response->json();
            if($data['status']=='finished'){
                Orders::where('id', $request->order_id)->update([
                    'order_status' => 'complete'
                ]);
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => 'completed'
                ], 200);
            }else{
                return response()->json([
                    'data' => $data,
                    'status' => true,
                    'message' => ''
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
