<?php

namespace App\Http\Controllers;

use App\Models\CollectedDetails;
use App\Models\Factory;
use App\Models\Location;
use App\Models\Transfer;
use App\Models\TransferDetails;
use App\Models\User;
use DB;
use Http;
use Illuminate\Http\Request;
use App\Models\SortedTransfer;
use App\Models\SortDetails;
use App\Models\Total;
use Auth;
use Illuminate\Support\Carbon;

class SortedTransferController extends Controller
{
    public $successStatus = true;
    public $FailedStatus = false;


    // public function (Request $request)
    // {
    //     try {
    //         $result = ($request->Clean_Clear + $request->Others + $request->Green_Colour + $request->Trash + $request->Caps);

    //         $t = SortDetails::where('location_id', Auth::user()->location_id)->first();
    //         $sorted = ($t->Clean_Clear + $t->Others + $t->Green_Colour + $t->Trash + $t->Caps);
    //         if(empty($sorted)){
    //             return response()->json([
    //                 'status' => $this->FailedStatus,
    //                 'message'    => 'No Collection Found',
    //             ],500 );
    //         }

    //             if($result > $sorted){
    //                 return response()->json([
    //                     'status' => $this->FailedStatus,
    //                     'message'    => 'Insufficent Sorted ',
    //                 ], 500);
    //             }
    //             $checkSort = SortDetails::where('location_id', Auth::user()->location_id)->first();
    //              if (empty($checkSort)) {
    //                 return response()->json([
    //                     'status' => $this->FailedStatus,
    //                     'message'    => 'No Collection Record Found',
    //                 ],500 );
    //              }
    //             if ($request->Clean_Clear > $checkSort->Clean_Clear) {
    //                 return response()->json([
    //                     'status' => $this->FailedStatus,
    //                     'message'    => 'Insufficent Clean Clear',
    //                 ], 500);
    //             }elseif ($request->Green_Colour > $checkSort->Green_Colour) {
    //                 return response()->json([
    //                     'status' => $this->FailedStatus,
    //                     'message'    => 'Insufficent  Green Colour',
    //                 ], 500);
    //             }elseif ($request->Others > $checkSort->Others) {
    //                 return response()->json([
    //                     'status' => $this->FailedStatus,
    //                     'message'    => 'Insufficent Others',
    //                 ], 500);
    //             }elseif ($request->Trash > $checkSort->Trash) {
    //                 return response()->json([
    //                     'status' => $this->FailedStatus,
    //                     'message'    => 'Insufficent Trash',
    //                 ], 500);
    //             }elseif ($request->Caps > $checkSort->Caps) {
    //                 return response()->json([
    //                     'status' => $this->FailedStatus,
    //                     'message'    => 'Insufficent Caps',
    //                 ], 500);
    //             }

    //         $sortedTransfer = new SortedTransfer();
    //         $sortedTransfer->item_id = $request->item_id;
    //         $sortedTransfer->Clean_Clear = $request->Clean_Clear ?? 0;
    //         $sortedTransfer->Green_Colour = $request->Green_Colour ?? 0;
    //         $sortedTransfer->Others = $request->Others ?? 0;
    //         $sortedTransfer->Trash = $request->Trash ?? 0;
    //         $sortedTransfer->Caps = $request->Caps ?? 0;
    //         $sortedTransfer->formLocation = Auth::user()->location_id;
    //         $sortedTransfer->toLocation = $request->toLocation ?? 0;
    //         $sortedTransfer->location_id = Auth::user()->location_id;
    //         $sortedTransfer->user_id = Auth::id();
    //         //dd($sortedTransfer);
    //         $sortedTransfer->save();


    //         $update = SortDetails::where('location_id', $request->toLocation)->first();
    //         if(empty($update)){
    //             $sortedTransfer = new SortDetails();
    //         $sortedTransfer->Clean_Clear = $request->Clean_Clear ?? 0;
    //         $sortedTransfer->Green_Colour = $request->Green_Colour ?? 0;
    //         $sortedTransfer->Others = $request->Others ?? 0;
    //         $sortedTransfer->Trash = $request->Trash ?? 0;
    //         $sortedTransfer->Caps = $request->Caps ?? 0;
    //         $sortedTransfer->location_id = $request->toLocation;
    //         $sortedTransfer->user_id = Auth::id();
    //         //dd($sortedTransfer);
    //         $sortedTransfer->save();
    //         }else{
    //             $updated = SortDetails::where('location_id', $request->toLocation)->first();
    //         $updated->update(['Clean_Clear' => ($updated->Clean_Clear + $request->Clean_Clear ?? 0)]);
    //         $updated->update(['Green_Colour' => ($updated->Green_Colour +$request->Green_Colour ?? 0)]);
    //         $updated->update(['Others' => ($updated->Others + $request->Others ?? 0)]);
    //         $updated->update(['Trash' => ($updated->Trash + $request->Trash ?? 0)]);
    //         $updated->update(['Caps' => ($updated->Caps + $request->Caps ?? 0)]);
    //         }



    //         $updated = SortDetails::where('location_id', Auth::user()->location_id)->first();
    //         $updated->update(['Clean_Clear' => ($updated->Clean_Clear - $request->Clean_Clear ?? 0)]);
    //         $updated->update(['Green_Colour' => ($updated->Green_Colour - $request->Green_Colour ?? 0)]);
    //         $updated->update(['Others' => ($updated->Others - $request->Others ?? 0)]);
    //         $updated->update(['Trash' => ($updated->Trash - $request->Trash ?? 0)]);
    //         $updated->update(['Caps' => ($updated->Caps - $request->Caps ?? 0)]);

    //         return  response()->json([
    //             "status" => $this->successStatus,
    //             "message" => "Successful",
    //             "data" => $sortedTransfer
    //         ],200);

    //     } catch (Exception $e) {
    //         return response()->json([
    //             "status" => $this->FailedStatus,
    //             "message" => $e,
    //         ], 500);
    //     }
    // }



    public function sortedTransfer(request $request)
    {


    try {

        $get_items_weignt = ($request->Clean_Clear ?? 0 + $request->Others ?? 0 + $request->Green_Colour ?? 0 + $request->Trash ?? 0+ $request->Caps ?? 0 + $request->hdpe ?? 0 + $request->ldpe ?? 0 + $request->brown ?? 0 + $request->black ?? 0);


        $t = SortDetails::where('location_id', Auth::user()->location_id)->first() ?? null;

        if($t == null) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'No Record Found',
            ], 500);

        }

        $bailed = ($t->Clean_Clear ?? 0 + $t->Others ?? 0  +   $t->Green_Colour ?? 0 + $t->Trash ?? 0 + $t->Caps ?? 0 + $t->hdpe ?? 0 + $t->ldpe ?? 0 + $t->brown ?? 0 + $t->black ?? 0 );
        if(empty($t)) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'No Record Found',
            ], 500);

        }

        if($t->location_id == $request->toLocation) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'You can not transfer to this Location',
            ], 500);
        }
        if($get_items_weignt > $bailed) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'Insufficent Sorted Items',
            ], 500);
        }
        $checkSort = SortDetails::where('location_id', Auth::user()->location_id)->first();
        if (empty($checkSort)) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'No Collection Found',
            ], 500);
        }
        if (($request->Clean_Clear ?? 0)> $checkSort->Clean_Clear) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'Insufficent Clean Clear Items',
            ], 500);
        } elseif (($request->Green_Colour ?? 0) > $checkSort->Green_Colour) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'Insufficent  Green Colour Items',
            ], 500);
        } elseif (($request->Others ?? 0)> $checkSort->Others) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'Insufficent Others Items',
            ], 500);
        } elseif (($request->Trash ?? 0) > $checkSort->Trash) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'Insufficent Trash Items',
            ], 500);
        } elseif (($request->hdpe ?? 0) > $checkSort->hdpe) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'Insufficent  HDPE Items',
            ], 500);
        } elseif (($request->ldpe ?? 0)> $checkSort->ldpe) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'Insufficent LDPE Items',
            ], 500);
        } elseif (($request->brown ?? 0) > $checkSort->brown) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'Insufficent BROWN Items',
            ], 500);
        } elseif (($request->black ?? 0) > $checkSort->black) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'Insufficent BLACK Items',
            ], 500);
        }



        $from = Location::where('id', Auth::user()->location_id)->first()->name;
        $to = Location::where('id', $request->toLocation)->first()->name;



        $transfer = new Transfer();
        $transfer->Clean_Clear = $request->Clean_Clear ?? 0;
        $transfer->Green_Colour = $request->Green_Colour ?? 0;
        $transfer->Others = $request->Others ?? 0;
        $transfer->Trash = $request->Trash ?? 0;
        $transfer->hdpe = $request->hdpe ?? 0;
        $transfer->ldpe = $request->ldpe ?? 0;
        $transfer->brown = $request->brown ?? 0;
        $transfer->black = $request->black ?? 0;
        $transfer->to_location = $to;
        $transfer->from_location = $from;
        $transfer->Caps = $request->Caps ?? 0;
        $transfer->clean_clear_qty = $request-> Clean_Clear?? 0;
        $transfer->green_color_qty = $request->Green_Colour?? 0;
        $transfer->other_qty = $request->Others ?? 0;
        $transfer->trash_qty = $request->Trash ?? 0;
        $transfer->hdpe_qty = $request-> hdpe_qty ?? 0;
        $transfer->ldpe_qty = $request->ldpe_qty?? 0;
        $transfer->black_qty = $request->black_qty ?? 0;
        $transfer->brown_qty = $request->brown_qty ?? 0;
        $transfer->user_id = Auth::id();
        $transfer->status = 0;
        $transfer->save();


        $transfered = ($transfer->Clean_Clear + $transfer->Others + $transfer->Green_Colour + $transfer->Trash + $transfer->ldpe + $transfer->hdpe + $transfer->brown + $transfer->black);

        $dataset = [
        'Clean_Clear' => $request->Clean_Clear ?? 0,
        'Green_Colour' => $request->Green_Colour ?? 0,
        'Others' => $request->Others ?? 0,
        'Trash' => $request->Trash ?? 0,
        'ldpe' => $request->ldpe ?? 0,
        'hdpe' => $request->hdpe ?? 0,
        'Caps' => $request->Caps ?? 0,
        'black' => $request->black ?? 0,
        'brown' => $request->brown ?? 0
        ];

        $other_value_history = [
            'location_id'=> Auth::user()->location_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        $other_value = [
            'location_id'=> Auth::user()->location_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        $old_transfer = DB::table('transfer_details')->where('location_id', Auth::user()->location_id)->first();



        if(empty($old_transfer)) {

            DB::table('transfer_details')->insert([
                array_merge($dataset, $other_value)
            ]);

        } else {

            //dd($new_dataset);
            $updated = TransferDetails::where('location_id', Auth::user()->location_id)->first();
            $updated->update(['Clean_Clear' => ($updated->Clean_Clear + ($request->Clean_Clear ?? 0))]);
            $updated->update(['Green_Colour' => ($updated->Green_Colour +($request->Green_Colour ?? 0))]);
            $updated->update(['Others' => ($updated->Others + ($request->Others ?? 0))]);
            $updated->update(['Trash' => ($updated->Trash + ($request->Trash ?? 0))]);
            $updated->update(['hdpe' => ($updated->hdpe + ($request->hdpe ?? 0))]);
            $updated->update(['ldep' => ($updated->ldpe +($request->ldpe ?? 0))]);
            $updated->update(['black' => ($updated->black + ($request->black ?? 0))]);
            $updated->update(['brown' => ($updated->brown + ($request->brown ?? 0))]);
            $updated->update(['Caps' => ($updated->Caps + ($request->Caps ?? 0))]);

        }


        $up = SortDetails::where('location_id', Auth::user()->location_id)->first();
        $up->update(['Clean_Clear' => ($up->Clean_Clear - ($request->Clean_Clear ?? 0))]);
        $up->update(['Green_Colour' => ($up->Green_Colour -($request->Green_Colour ?? 0))]);
        $up->update(['Others' => ($up->Others - ($request->Others ?? 0))]);
        $up->update(['Trash' => ($up->Trash -  ($request->Trash ?? 0))]);
        $up->update(['hdpe' => ($up->hdpe -  ($request->hdpe ?? 0))]);
        $up->update(['ldep' => ($up->ldpe - ($request->ldpe ?? 0)) ]);
        $up->update(['black' => ($up->black -  ($request->black ?? 0))]);
        $up->update(['brown' => ($up->brown - ($request->brown ?? 0))]);
        $up->update(['Caps' => ($up->Caps  - ($request->Caps ?? 0))]);


        $up1 = SortDetails::where('location_id', $request->toLocation)->first();
        $up1->update(['Clean_Clear' => ($up1->Clean_Clear + ($request->Clean_Clear ?? 0))]);
        $up1->update(['Green_Colour' => ($up1->Green_Colour + ($request->Green_Colour ?? 0))]);
        $up1->update(['Others' => ($up1->Others + ($request->Others ?? 0))]);
        $up1->update(['Trash' => ($up1->Trash +  ($request->Trash ?? 0))]);
        $up1->update(['hdpe' => ($up1->hdpe  +  ($request->hdpe ?? 0))]);
        $up1->update(['ldep' => ($up1->ldpe + ($request->ldpe ?? 0)) ]);
        $up1->update(['black' => ($up1->black +  ($request->black ?? 0))]);
        $up1->update(['brown' => ($up1->brown + ($request->brown ?? 0))]);
        $up1->update(['Caps' => ($up1->Caps  + ($request->Caps ?? 0))]);


        
        




        $notification_id = User::where('factory_id', $request->factory_id)
            ->whereNotNull('device_id')
            ->pluck('device_id');
        //dd($notification_id);
        if (!empty($notification_id)) {

            $factory = Factory::where('id', Auth::user()->location_id)->first();
            $response = Http::withHeaders([
                'Authorization' => 'key=AAAAva2Kaz0:APA91bHSiOJFPwd-9-2quGhhiyCU263oFWWrnYKtmuF1jGmDSMBHWiFkGy3tiaP3bLhJNMy9ki0YY061y5riGULckZtBkN9WkDZGX5X9HN60a2NvwHFR8Yevnat_zHzomC5O7AkdYwT8',
                'Content-Type' => 'application/json'
            ])->post('https://fcm.googleapis.com/fcm/send', [
                "registration_ids" => $notification_id,
                     "notification" => [
                                "title" => "Transfer notification",
                                "body" => "Incomming Transfer from ".$request->factory_id
                            ]
            ]);
            $notification = $response->json('results');
        }


        return response()->json([
            "status" => $this->successStatus,
            "message" => "Transfer created Successful",
            "data" => $transfer,
            // "total" => $t->transfered,
            // "total_bailed" => $t->bailed,
            // "notification" => $notification
        ], 200);



    } catch (Exception $e) {
        return response()->json([
            'status' => $this->FailedStatus,
            'message'    => 'Error',
            'errors' => $e->getMessage(),
        ], 500);
    }





    }



    public function unsortedTransfer(Request $request)
    {
        try {


            $check_collected = CollectedDetails::where('location_id', Auth::user()->location_id)->first()->collected;
            $check_loction = CollectedDetails::where('location_id', Auth::user()->location_id)->first()->location_id;



            if($check_collected < $request->item_weight){

                return response()->json([
                    'status' => $this->FailedStatus,
                    'message'    => 'Insufficent Unsorted Materials',
                ], 500);

            }

            if($check_loction == $request->toLocation){

                return response()->json([
                    'status' => $this->FailedStatus,
                    'message'    => 'You can not transfer material to yourself',
                ], 500);

            }


           CollectedDetails::where('location_id', Auth::user()->location_id)->decrement('collected', $request->unsorted);

           $ck_location = CollectedDetails::where('location_id', $request->toLocation)->first()->location_id ?? null;

           if($ck_location == null){
            $location = new CollectedDetails();
            $location->location_id = $request->toLocation;
            $location->user_id = Auth::id();
            $location->save();
           }

           CollectedDetails::where('location_id', $request->toLocation)->increment('collected', $request->unsorted);

           $collect = new Transfer();
           $collect->unsorted = $request->unsorted;
           $collect->user_id = Auth::id();
           $collect->to_location = $request->toLocation;
           $collect->from_location = Auth::user()->location_id;
           $collect->save();


           $data = CollectedDetails::where('location_id', Auth::user()->location_id)->first()->collected;

           $weight_transfred = CollectedDetails::where('location_id', $request->toLocation)->first()->collected;



            return response()->json([
                "status" => $this->successStatus,
                "message" => "Transfer created Successful",
                "data" => $data,
                "weight_transfred" => $weight_transfred,
            ],200);

        } catch (Exception $e) {
            return response()->json([
                "status" => $this->FailedStatus,
                "message" => $e,
            ], 500);
        }
    }

    public function transfer(Request $request){


        try{
            $result = ($request->Clean_Clear + $request->Others + $request->Green_Colour + $request->Trash);
                $t = SortDetails::where('location_id', Auth::user()->location_id)->first();
            $sorted = ($t->Clean_Clear + $t->Others + $t->Green_Colour + $t->Trash + $t->Caps);
                if(empty($sorted)){
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'No Record Found',
                    ], 500);

                }

                    if($result > $sorted){
                        return response()->json([
                            'status' => $this->FailedStatus,
                            'message'    => 'Insufficent Sorted Items',
                        ], 500);
                    }
                    $checkSort = SortDetails::where('location_id', Auth::user()->location_id)->first();
                 if (empty($checkSort)) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'No Collection Found',
                    ],500 );
                 }
                if ($request->Clean_Clear > $checkSort->Clean_Clear) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Clean Clear Items',
                    ], 500);
                }elseif ($request->Green_Colour > $checkSort->Green_Colour) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent  Green Colour Items',
                    ], 500);
                }elseif ($request->Others > $checkSort->Others) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Others Items',
                    ], 500);
                }elseif ($request->Trash > $checkSort->Trash) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Trash Items',
                    ], 500);
                }


                    $transfer = new Transfer();
                    $transfer->Clean_Clear = $request->Clean_Clear ?? 0;
                    $transfer->Green_Colour = $request->Green_Colour ?? 0;
                    $transfer->Others = $request->Others ?? 0;
                    $transfer->Trash = $request->Trash ?? 0;
                    $transfer->Caps = $request->Caps ?? 0;
                    $transfer->location_id = Auth::user()->location_id;
                    $transfer->factory_id = $request->factory_id;
                    $transfer->collection_id = Auth::user()->location_id;
                    $transfer->user_id = Auth::id();
                    $transfer->status = 0;
                    //dd($transfer);
                    $transfer->save();


                    $transfered = ($transfer->Clean_Clear + $transfer->Others + $transfer->Green_Colour + $transfer->Trash);
                    // $total = Total::where('location_id',Auth::user()->location_id)->first();
                    // $old_total_transfered = $total->transfered;
                    // $total->update(['transfered' => ($total->transfered + $transfered)]);
                    // $total->update(['sorted' => ($total->sorted - $transfered)]);




                        $dataset = [
                        'Clean_Clear' => $request->Clean_Clear ?? 0,
                        'Green_Colour' => $request->Green_Colour ?? 0,
                        'Others' => $request->Others ?? 0,
                        'Trash' => $request->Trash ?? 0
                        ];
                        //dd($tweight);

                        $other_value_history = [
                            'location_id'=> Auth::user()->location_id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ];
                        $other_value = [
                            'location_id'=> Auth::user()->location_id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ];

                        $old_transfer = DB::table('transfer_details')->where('location_id', Auth::user()->location_id)->first();

                        if(empty($old_transfer)){

                            DB::table('transfer_details')->insert([
                                array_merge($dataset, $other_value)
                            ]);
                        }else{

                            //dd($new_dataset);
                            $updated = TransferDetails::where('location_id', Auth::user()->location_id)->first();
                           $updated->update(['Clean_Clear' => ($updated->Clean_Clear + $request->Clean_Clear ?? 0)]);
                           $updated->update(['Green_Colour' => ($updated->Green_Colour +$request->Green_Colour ?? 0)]);
                           $updated->update(['Others' => ($updated->Others + $request->Others ?? 0)]);
                           $updated->update(['Trash' => ($updated->Trash + $request->Trash ?? 0)]);
                           $updated->update(['Caps' => ($updated->Caps + $request->Caps ?? 0)]);
                        }

                        $updated = SortDetails::where('location_id', Auth::user()->location_id)->first();
                        //dd($updated->Clean_Clear);
                        $updated->update(['Clean_Clear' => ($updated->Clean_Clear - $request->Clean_Clear ?? 0)]);
                        $updated->update(['Green_Colour' => ($updated->Green_Colour - $request->Green_Colour ?? 0)]);
                        $updated->update(['Others' => ($updated->Others - $request->Others ?? 0)]);
                        $updated->update(['Trash' => ($updated->Trash - $request->Trash ?? 0)]);
                        $updated->update(['Caps' => ($updated->Caps - $request->Caps ?? 0)]);



                    $notification_id = User::where('factory_id',$request->factory_id)
                        ->whereNotNull('device_id')
                        ->pluck('device_id');
                        //dd($notification_id);
                    if (!empty($notification_id)) {

                        $factory = Factory::where('id',Auth::user()->location_id)->first();
                        $response = Http::withHeaders([
                            'Authorization' => 'key=AAAAva2Kaz0:APA91bHSiOJFPwd-9-2quGhhiyCU263oFWWrnYKtmuF1jGmDSMBHWiFkGy3tiaP3bLhJNMy9ki0YY061y5riGULckZtBkN9WkDZGX5X9HN60a2NvwHFR8Yevnat_zHzomC5O7AkdYwT8',
                            'Content-Type' => 'application/json'
                        ])->post('https://fcm.googleapis.com/fcm/send', [
                            "registration_ids" => $notification_id,
                                 "notification" => [
                                            "title" => "Transfer notification",
                                            "body" => "Incomming Transfer from ".$factory->name
                                        ]
                        ]);
                        $notification = $response->json('results');
                    }


                    return response()->json([
                        "status" => $this->successStatus,
                        "message" => "Transfer created Successful",
                        "data" => $transfer,
                        "total" => $t->transfered,
                        "total_bailed" => $t->bailed,
                        "notification" => $notification
                    ],200);

            } catch (Exception $e) {
                return response()->json([
                    'status' => $this->FailedStatus,
                    'message'    => 'Error',
                    'errors' => $e->getMessage(),
                ], 500);
            }


    }

    public function getSortedTransfer()
    {
        $getsortedtransfer = SortedTransfer::where('location_id',Auth::user()->location_id)->get();
        return  response()->json([
                "status" => $this->successStatus,
                "data" => $getsortedtransfer
            ],200);
    }
}
