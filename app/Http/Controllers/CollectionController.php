<?php

namespace App\Http\Controllers;

use App\Models\CollectedDetails;
use App\Models\Collection;
use App\Models\DropOff;
use App\Models\Greeting;
use App\Models\Location;
use App\Models\PlasticWaste;
use App\Models\Rate;
use App\Models\State;
use App\Models\StateLga;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mail;

class CollectionController extends Controller
{
    //
    public $SuccessStatus = true;
    public $FailedStatus = false;

    public function collect(Request $request)
    {

        $collect = new Collection();
        $collect->item_id = $request->input('item');
        $collect->item_weight = $request->input('item_weight') ?? 0;
        $collect->price_per_kg = $request->input('price_per_kg') ?? 0;
        $collect->transport = $request->input('transport') ?? 0;
        $collect->loader = $request->input('loader') ?? 0;
        $collect->others = $request->input('others') ?? 0;
        $collect->location_id = Auth::user()->location_id;
        $collect->amount = $request->input('amount') ?? 0;
        $collect->user_id = Auth::id();
        $collect->save();

        $collected = $request->input('item_weight') ?? 0;
        $locationId = Auth::user()->location_id;

        $t = CollectedDetails::where('location_id', Auth::user()->location_id)->first();
        if (empty($t)) {
            $sort = new CollectedDetails();
            $sort->collected = $request->input('item_weight') ?? 0;
            $sort->location_id = Auth::user()->location_id;
            $sort->user_id = Auth::id();
            $sort->save();
        } else {
            CollectedDetails::where('location_id', Auth::user()->location_id)->increment('collected', $collected);
        }

        return response()->json([
            "status" => $this->SuccessStatus,
            "message" => "Collection created successfull",
            "data" => $collect,
            "total" => $t->collected,
        ], 200);
    }

    public function getCollection(Request $request)
    {
        try {
            $collect = Collection::with('location', 'item')
                ->where('location_id', Auth::user()->location_id)
                ->get();

            return response()->json([
                "status" => $this->SuccessStatus,
                "message" => "Successfull",
                "data" => $collect,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => $this->failedStatus,
                'msg' => 'Error',
                'errors' => $e->getMessage(),
            ], 401);
        }

    }

    public function get_plastic_waste(Request $request)
    {
        try {

            $rate = Rate::all();
            $plasticwaste = PlasticWaste::all();

            return response()->json([
                "status" => $this->SuccessStatus,
                "message" => "Successfull",
                "waste" => $plasticwaste,
                "rate" => $rate,

            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => $this->failedStatus,
                'msg' => 'Error',
                'errors' => $e->getMessage(),
            ], 401);
        }

    }

//get all state
    public function all_state(Request $request)
    {
        try {
            $state = State::all();

            return response()->json([
                "status" => $this->SuccessStatus,
                "message" => "Successful",
                "data" => $state,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => $this->failedStatus,
                'msg' => 'Error',
                'errors' => $e->getMessage(),
            ], 401);
        }

    }

    //get lga by state
    public function get_lga(Request $request)
    {

        $state = $request->all();

        try {

            $result = StateLga::where('state', $state)
                ->get();

            return response()->json([
                "status" => $this->SuccessStatus,
                "message" => "Successfull",
                "data" => $result,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => $this->failedStatus,
                'msg' => 'Error',
                'errors' => $e->getMessage(),
            ], 401);
        }

    }

    public function drop_off(Request $request)
    {

        $collection_center_id = $request->collection_center;

        $sender_id = Auth::id();
        $receiver_id = $collection_center_id;

        //get collection_center(location)
        $get_location = Location::where('id', $collection_center_id)
            ->first();

        //get collection center number
        $get_agent_user_id = Location::where('id', $collection_center_id)
        ->first()->user_id;

        $agent_phone = User::where('id', $get_agent_user_id)
            ->first()->phone;

        $customer_phone = User::where('id', Auth::id())
            ->first()->phone;


        $customer_gender = User::where('id', Auth::id())
            ->first()->gender;

        $location_name = $get_location->name;
        $location_address = $get_location->address;
        $agent_log = $get_location->longitude;
        $agent_lat = $get_location->latitude;

        //get user address
        $address = Auth::user()->address;
        $state = Auth::user()->state;
        $lga = Auth::user()->lga;
        $city = Auth::user()->city;
        $long = Auth::user()->long;
        $lat = Auth::user()->lat;

        //get rate
        $get_rate = Rate::where('id', 1)->first();
        $rate = $get_rate->rate;

        //get weight
        // $get_weight = PlasticWaste::where('id', $weight_id)
        // ->first();

        // $plastic_weight_name = $get_weight->name;
        // $plastic_weight = $get_weight->weight;

        //calculate amount
        // $amount = $rate * $plastic_weight;
        if ($sender_id == $receiver_id) {
            return response()->json([
                'status' => $this->FailedStatus,
                'msg' => 'You cant send drop off to yourself',
            ], 500);

        } else {

            function generateRandomString($length = 6)
            {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < $length; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }
                return $randomString;
            }

            $order_id = generateRandomString();

            $drop = new DropOff();
            $drop->order_id = $order_id;
            $drop->city = $city;
            $drop->lat = $lat;
            $drop->long = $long;
            $drop->address = $address;
            $drop->lga = $lga;
            $drop->state = $state;
            $drop->sender_id = Auth::id();
            $drop->receiver_id = $collection_center_id;

            // if($file = $request->hasFile('image')) {
            //     $file = $request->file('image') ;
            //     $fileName = $file->getClientOriginalName() ;
            //     $destinationPath = public_path().'upload/customer' ;
            //     $request->image->move(public_path('upload/customer'),$fileName);
            //     $drop->image = $fileName ;
            // }

            $drop->status = 0;
            $drop->collection_center = $location_name;
            $drop->user_id = Auth::id();
            $drop->customer = Auth::user()->first_name . " " . Auth::user()->last_name;
            // $drop->waste_weight = $plastic_weight;
            $drop->agent_log = $agent_log;
            $drop->agent_lat = $agent_lat;
            $drop->agent_phone = $agent_phone;
            $drop->customer_phone = $customer_phone;

            $drop->save();

            $user_firebaseToken = User::where('id', Auth::id())
                ->first()->device_id;

            $SERVER_API_KEY = env('FCM_SERVER_KEY');
            $data = [
                "registration_ids" => array($user_firebaseToken),
                "notification" => [
                    "title" => 'Drop Off Created',
                    "body" => "Your order has been successfully created. Head to collection center to Drop off your Plastic waste.",
                ],

                "data" => [
                    "message" => "Your order has been successfully created",
                ],

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

            //send to Agent
            $get_agent_firebaseToken = User::where('location_id', $collection_center_id)
                ->first();
            $agent_firebaseToken = $get_agent_firebaseToken->device_id;

            $SERVER_API_KEY = env('FCM_SERVER_KEY');

            $data = [
                "registration_ids" => array($agent_firebaseToken),
                "notification" => [
                    "title" => 'Drop Off Created',
                    "body" => "An order is coming your way. Get ready to receive the order",
                ],

                "data" => [
                    "message" => "Incoming Order",
                ],
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

            $get_response = curl_exec($ch);

            $user_email = Auth()->user();
            $receiveremail = $user_email->email;

            if($customer_gender = 'Male'){

                $greeting = Greeting::where('gender', 'Male' )
                ->first()->title;
            }else{

                $greeting = Greeting::where('gender', 'Female' )
                ->first()->title;

            }

            //send email to sender
            $data = array(
                'fromsender' => 'notification@kaltanimis.com', 'KALTANI',
                'subject' => "New Drop Off",
                'toreceiver' => $receiveremail,
                'greeting' => $greeting,
                'order_id' => $order_id,

            );

            Mail::send('dropoff', ["data1" => $data], function ($message) use ($data) {
                $message->from($data['fromsender']);
                $message->to($data['toreceiver']);
                $message->subject($data['subject']);

            });

            $get_receiver_email = User::where('location_id', $collection_center_id)
                ->first();
            $receiveremail = $get_receiver_email->email;

            //send email to receiver
            $data = array(
                'fromsender' => 'notification@kaltanimis.com', 'KALTANI',
                'subject' => "New Drop Off",
                'toreceiver' => $receiveremail,
                'order' => $order_id,
            );

            Mail::send('agentdropoff', ["data1" => $data], function ($message) use ($data) {
                $message->from($data['fromsender']);
                $message->to($data['toreceiver']);
                $message->subject($data['subject']);

            });

            return response()->json([
                "status" => $this->SuccessStatus,
                "message" => "Drop Off created successfully",
                "data" => $drop,
            ], 200);

        }

    }

    public function drop_off_list(Request $request)
    {

        $transations = DropOff::where('sender_id', Auth::id())
            ->take(10)->get();

        return response()->json([

            'success' => $this->SuccessStatus,
            'data' => $transations,
        ], 200);

    }

    public function delete_drop_off(Request $request)
    {
        $order_id = $request->order_id;

        $delete = DropOff::where('order_id', $order_id)
            ->delete();

        return response()->json([

            'status' => $this->SuccessStatus,
            'message' => 'Your order has been deleted successfully',
        ], 200);

    }

    // public function update_drop_off(Request $request)
    // {

    //     $order_id = $request->order_id;

    //     $get_location_id = DropOff::where('order_id', $order_id)
    //         ->first();
    //     $receiver_id = $get_location_id->receiver_id;

    //     $get_user_id = DropOff::where('order_id', $order_id)
    //         ->first();
    //     $user_id = $get_user_id->user_id;

    //     $file = $request->file('agent_image');
    //     $fileName = $file->getClientOriginalName();
    //     $destinationPath = public_path() . 'upload/agent';
    //     $request->agent_image->move(public_path('upload/agent'), $fileName);

    //     try {

    //         DropOff::where('order_id', $order_id)
    //             ->update([
    //                 'status' => 2,
    //                 'agent_image' => $fileName,
    //             ]);

    //         return response()->json([
    //             "status" => $this->SuccessStatus,
    //             "message" => "Collection successfully updated ",

    //         ], 200);

    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => $this->failedStatus,
    //             'msg' => 'Error',
    //             'errors' => $e->getMessage(),
    //         ], 401);
    //     }

    // }

    public function nearest_location(REQUEST $request)
    {

        $state = Auth::user()->state;
        $lga = Auth::user()->lga;
        $city = Auth::user()->city;

        $location = Location::where([
            'state' => $state,
            'lga' => $lga,
            'city' => $city,
        ])->get();

        return response()->json([
            "status" => $this->SuccessStatus,
            "location" => $location,
        ], 200);

    }

    public function location_by_state(REQUEST $request)
    {

        $state = Auth::user()->state;

        $location = Location::where([
            'state' => $state,
        ])->get();

        return response()->json([
            "status" => $this->SuccessStatus,
            "location" => $location,
        ], 200);

    }

    public function location_by_lga(REQUEST $request)
    {

        $lga = Auth::user()->lga;

        $location = Location::where([
            'lga' => $lga,
        ])->get();

        return response()->json([
            "status" => $this->SuccessStatus,
            "location" => $location,
        ], 200);

    }

    public function location_by_city(REQUEST $request)
    {

        $city = Auth::user()->city;

        $location = Location::where([
            'city' => $city,
        ])->get();

        return response()->json([
            "status" => $this->SuccessStatus,
            "location" => $location,
        ], 200);

    }

    public function agent_waste_list(REQUEST $request)
    {

        $id = Auth::user()->id;
        $get_user = User::where([
            'id' => $id,
            'user_type' => 'agent',
        ])->first();

        $drop_off = DropOff::where('receiver_id', $get_user->location_id)
            ->get();


        $total = Collection::where('location_id', $get_user->location_id)
        ->sum('item_weight');



        return response()->json([
            "status" => $this->SuccessStatus,
            "drop_off" => $drop_off,
            "total_weight" => $total,

        ], 200);

    }

    public function update_dropoff_weight(REQUEST $request)
    {

        $order_id = $request->order_id;
        $weight = $request->weight;
        $amount = $request->amount;

        $status = DropOff::where('order_id', $order_id)
            ->first()->status;

        if ($status == 1) {
            return response()->json([
                "status" => $this->FailedStatus,
                "message" => "Order has already been confirmed",
            ], 500);
        }

        $file = $request->file('agent_image');
        $fileName = $file->getClientOriginalName();
        $destinationPath = public_path() . 'upload/agent';
        $request->agent_image->move(public_path('upload/agent'), $fileName);

        $update = DropOff::where('order_id', $order_id)
            ->update([
                'weight' => $weight,
                'waste_weight' => $weight . " " . 'KG',
                'amount' => $amount,
                'status' => 2,
                'agent_image' => $fileName,

            ]);

        return response()->json([
            "status" => $this->SuccessStatus,
            "message" => "Drop Off Successfully Updated",
        ], 200);

    }





}
