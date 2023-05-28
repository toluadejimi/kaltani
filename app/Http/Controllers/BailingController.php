<?php

namespace App\Http\Controllers;

use App\Models\BailedDetail;
use App\Models\Factory;
use App\Models\Location;
use App\Models\Transfer;
use App\Models\TransferDetails;
use App\Models\UnSortDetails;
use App\Models\UnsortedBailing;
use App\Models\User;
use Http;
use Illuminate\Http\Request;
use App\Models\Bailing;
use App\Models\Total;
use Illuminate\Http\Response;
use Auth;
use App\Models\BailingItem;
use App\Models\BailedDetails;
use App\Models\SortDetails;
use Carbon\Carbon;
use DB;
use App\Http\Traits\HistoryTrait;
use App\Models\CollectedBailedDetails;
use App\Models\Item;

class BailingController extends Controller
{
    //
    use HistoryTrait;
    public $successStatus = true;
    public $FailedStatus = false;

    public function getBailing(Request $request)
    {

        $total = SortDetails::where('location_id', Auth::user()->location_id)->first();
        if(empty($total)) {
            return response()->json([
               "status" => $this->FailedStatus,
               "message" => "No material available for this center",
            ], 500);
        }
        $tsorted = ($total->Clean_Clear + $total->Others + $total->Green_Colour + $total->Trash);
        $bailing_items = BailingItem::all();
        $getBailing = Bailing::where('location_id', Auth::user()->location_id)->get();
        $sorted = SortDetails::where('location_id', Auth::user()->location_id)->first();
        $items = Item::all();

        if(isset($total)) {
            return response()->json([
            "status" => $this->successStatus,
            "message" => "Bailing created Successful",
            "items" => $items,
            "sorted_breakdown" => $sorted,
            "bailing_item" => $bailing_items,
            "total_sorted" => (string)$tsorted
        ], 200);

        }

        return response()->json([
                "status" => $this->FailedStatus,
                "message" => "No material available for this center",
            ], 500);

    }

    public function getUnsortedBailing(Request $request)
    {

        $total = UnSortDetails::where('location_id', Auth::user()->location_id)->first();
        if(empty($total)) {
            return response()->json([
               "status" => $this->FailedStatus,
               "message" => "No material available for this center",
            ], 500);
        }
        $tsorted = ($total->Clean_Clear + $total->Others + $total->Green_Colour + $total->Trash);
        $bailing_items = BailingItem::select('item', 'items_id')->get();
        ;
        $getBailing = Bailing::where('location_id', Auth::user()->location_id)->get();
        $sorted = UnSortDetails::where('location_id', Auth::user()->location_id)->first();
        $items = Item::select('item', 'id')->get();

        if(isset($total)) {
            return response()->json([
            "status" => $this->successStatus,
            "items" => $items,
            "sorted_breakdown" => $sorted,
            "bailing_item" => $bailing_items,
            "total_unsorted" => (string)$tsorted
        ], 200);

        }

        return response()->json([
                "status" => $this->FailedStatus,
                "message" => "No material available for this center",
            ], 500);

    }

    public function bailing(Request $request)
    {



        try {


            $type = $request->type;

            if($type == 'sorted_balied') {

                $Clean_Clear = $request->Clean_Clear;
                $Others = $request-> Others;
                $Green_Colour = $request-> Green_Colour;
                $Trash = $request-> Trash;

                $result = $Clean_Clear + $Others + $Green_Colour + $Trash;

                //$result = ($request-> Clean_Clear  + $request-> Others  + $request-> Green_Colour  + $request-> Trash );



                $total = SortDetails::where('location_id', Auth::user()->location_id)->first();
                $tsorted = ($total->Clean_Clear + $total->Others + $total->Green_Colour + $total->Trash);
                if(empty($total)) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'No Collection Found',
                    ], 500);
                }

                if($result > $tsorted) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Sorted ',
                    ], 500);
                }
                $checkSort = SortDetails::where('location_id', Auth::user()->location_id)->first();
                if (empty($checkSort)) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'No Collection Record Found',
                    ], 500);
                }
                if ($request->Clean_Clear > $checkSort->Clean_Clear) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Clean Clear',
                    ], 500);
                } elseif ($request->Green_Colour > $checkSort->Green_Colour) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent  Green Colour',
                    ], 500);
                } elseif ($request->Others > $checkSort->Others) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Others',
                    ], 500);
                } elseif ($request->Trash > $checkSort->Trash) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Trash',
                    ], 500);
                }


                $bailing = new Bailing();
                $bailing->item_id = $request->item_id;
                $bailing->Clean_Clear = $request->Clean_Clear ?? 0;
                $bailing->Green_Colour = $request->Green_Colour ?? 0;
                $bailing->Others = $request->Others ?? 0;
                $bailing->Trash = $request->Trash ?? 0;
                $bailing->location_id = Auth::user()->location_id;
                $bailing->user_id = Auth::id();
                //dd($bailing);
                $bailing->save();


                // $bailed = ($bailing->Clean_Clear + $bailing->Others + $bailing->Green_Colour + $bailing->Trash);
                // $total = Total::where('location_id',Auth::user()->location_id)->first();
                // $old_total_bailed = $total-> bailed;
                // $total->update(['bailed' => ($total->bailed + $bailed)]);
                // $total->update(['sorted' => ($total->sorted - $bailed)]);


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


                $old_bailing = DB::table('bailed_details')->where('location_id', Auth::user()->location_id)->first();
                //dd(empty($old_sorting));
                if(empty($old_bailing)) {

                    DB::table('bailed_details')->insert([
                        array_merge($dataset, $other_value)
                    ]);
                } else {

                    $updated = BailedDetails::where('location_id', Auth::user()->location_id)->first();
                    //dd($updated->Clean_Clear);
                    $updated->update(['Clean_Clear' => ($updated->Clean_Clear + $request->Clean_Clear ?? 0)]);
                    $updated->update(['Green_Colour' => ($updated->Green_Colour +$request->Green_Colour ?? 0)]);
                    $updated->update(['Others' => ($updated->Others + $request->Others ?? 0)]);
                    $updated->update(['Trash' => ($updated->Trash + $request->Trash ?? 0)]);
                }

                $updated = SortDetails::where('location_id', Auth::user()->location_id)->first();
                //dd($updated->Clean_Clear);
                $updated->update(['Clean_Clear' => ($updated->Clean_Clear - $request->Clean_Clear ?? 0)]);
                $updated->update(['Green_Colour' => ($updated->Green_Colour - $request->Green_Colour?? 0)]);
                $updated->update(['Others' => ($updated->Others - $request->Others ?? 0)]);
                $updated->update(['Trash' => ($updated->Trash - $request->Trash ?? 0)]);

                $bailing_items = BailingItem::all();
                return response()->json([
                    "status" => $this->successStatus,
                    // "data" => $bailing,
                    // "total" => $t->bailed,
                    "message" => "Bailing created Successful",
                    "bailing_item" => $bailing_items,
                    // "total_sorted" => $t->sorted
                ], 200);


            }

            if($type == 'unsorted_balied') {

                $Clean_Clear = $request->Clean_Clear;
                $Others = $request-> Others;
                $Green_Colour = $request-> Green_Colour;
                $Trash = $request-> Trash;

                $result = $Clean_Clear + $Others + $Green_Colour + $Trash;

                //$result = ($request-> Clean_Clear  + $request-> Others  + $request-> Green_Colour  + $request-> Trash );



                $total = UnSortDetails::where('location_id', Auth::user()->location_id)->first();
                $tsorted = ($total->Clean_Clear + $total->Others + $total->Green_Colour + $total->Trash);
                if(empty($total)) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'No Collection Found',
                    ], 500);
                }

                if($result > $tsorted) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Unsorted ',
                    ], 500);
                }
                $checkSort = UnSortDetails::where('location_id', Auth::user()->location_id)->first();
                if (empty($checkSort)) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'No Collection Record Found',
                    ], 500);
                }
                if ($request->Clean_Clear > $checkSort->Clean_Clear) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Clean Clear',
                    ], 500);
                } elseif ($request->Green_Colour > $checkSort->Green_Colour) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent  Green Colour',
                    ], 500);
                } elseif ($request->Others > $checkSort->Others) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Others',
                    ], 500);
                } elseif ($request->Trash > $checkSort->Trash) {
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Trash',
                    ], 500);
                }


                $bailing = new UnsortedBailing();
                $bailing->item_id = $request->item_id;
                $bailing->Clean_Clear = $request->Clean_Clear ?? 0;
                $bailing->Green_Colour = $request->Green_Colour ?? 0;
                $bailing->Others = $request->Others ?? 0;
                $bailing->Trash = $request->Trash ?? 0;
                $bailing->location_id = Auth::user()->location_id;
                $bailing->user_id = Auth::id();
                //dd($bailing);
                $bailing->save();


                // $bailed = ($bailing->Clean_Clear + $bailing->Others + $bailing->Green_Colour + $bailing->Trash);
                // $total = Total::where('location_id',Auth::user()->location_id)->first();
                // $old_total_bailed = $total-> bailed;
                // $total->update(['bailed' => ($total->bailed + $bailed)]);
                // $total->update(['sorted' => ($total->sorted - $bailed)]);


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


                $old_bailing = DB::table('unsorted_bailed_details')->where('location_id', Auth::user()->location_id)->first();
                //dd(empty($old_sorting));
                if(empty($old_bailing)) {

                    DB::table('unsorted_bailed_details')->insert([
                        array_merge($dataset, $other_value)
                    ]);
                } else {

                    $updated = BailedDetails::where('location_id', Auth::user()->location_id)->first();
                    //dd($updated->Clean_Clear);
                    $updated->update(['Clean_Clear' => ($updated->Clean_Clear + $request->Clean_Clear ?? 0)]);
                    $updated->update(['Green_Colour' => ($updated->Green_Colour +$request->Green_Colour ?? 0)]);
                    $updated->update(['Others' => ($updated->Others + $request->Others ?? 0)]);
                    $updated->update(['Trash' => ($updated->Trash + $request->Trash ?? 0)]);
                }

                $updated = SortDetails::where('location_id', Auth::user()->location_id)->first();
                //dd($updated->Clean_Clear);
                $updated->update(['Clean_Clear' => ($updated->Clean_Clear - $request->Clean_Clear ?? 0)]);
                $updated->update(['Green_Colour' => ($updated->Green_Colour - $request->Green_Colour?? 0)]);
                $updated->update(['Others' => ($updated->Others - $request->Others ?? 0)]);
                $updated->update(['Trash' => ($updated->Trash - $request->Trash ?? 0)]);

                $bailing_items = BailingItem::all();
                return response()->json([
                    "status" => $this->successStatus,
                    // "data" => $bailing,
                    // "total" => $t->bailed,
                    "message" => "Bailing created Successful",
                    "bailing_item" => $bailing_items,
                    // "total_sorted" => $t->sorted
                ], 200);


            }

        } catch (Exception $e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'Error',
                'errors' => $e,
            ], 401);
        }
    }




    public function transfer_sorted_bailed(request $request)
    {


    try {

        $get_items_weignt = ($request->Clean_Clear ?? 0 + $request->Others ?? 0 + $request->Green_Colour ?? 0 + $request->Trash ?? 0+ $request->Caps ?? 0 + $request->hdpe ?? 0 + $request->ldpe ?? 0 + $request->brown ?? 0 + $request->black ?? 0);


        $t = BailedDetails::where('location_id', Auth::user()->location_id)->first() ?? null;

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
                'message'    => 'Insufficent Bailed Items',
            ], 500);
        }
        $checkSort = BailedDetails::where('location_id', Auth::user()->location_id)->first();
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
        $transfer->to_location_id = $to;
        $transfer->from_location_id = $from;
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


        $up = BailedDetails::where('location_id', Auth::user()->location_id)->first();


        $up->update(['Clean_Clear' => ($up->Clean_Clear - ($request->Clean_Clear ?? 0))]);
        $up->update(['Green_Colour' => ($up->Green_Colour -($request->Green_Colour ?? 0))]);
        $up->update(['Others' => ($up->Others - ($request->Others ?? 0))]);
        $up->update(['Trash' => ($up->Trash -  ($request->Trash ?? 0))]);
        $up->update(['hdpe' => ($up->hdpe -  ($request->hdpe ?? 0))]);
        $up->update(['ldep' => ($up->ldpe - ($request->ldpe ?? 0)) ]);
        $up->update(['black' => ($up->black -  ($request->black ?? 0))]);
        $up->update(['brown' => ($up->brown - ($request->brown ?? 0))]);
        $up->update(['Caps' => ($up->Caps  - ($request->Caps ?? 0))]);


        $up1 = BailedDetails::where('location_id', $request->toLocation)->first();
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



public function transfer_unsorted_bailed(request $request)
    {


    try {

        $unsorted = ($request->unsorted);
        $t = CollectedBailedDetails::where('location_id', Auth::user()->location_id)->first() ?? null;

        if($t == null) {
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



        if($unsorted > $t->collected) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'Insufficent Unsorted Bailed Items',
            ], 500);
        }


        // $checkSort = CollectedBailedDetails::where('location_id', Auth::user()->location_id)->first();
        // if (empty($checkSort)) {
        //     return response()->json([
        //         'status' => $this->FailedStatus,
        //         'message'    => 'No Collection center found to send bailed items',
        //     ], 500);
        // }

        // $checkSort = CollectedBailedDetails::where('location_id', $request->toLocation)->first();
        // if (empty($checkSort)) {
        //     return response()->json([
        //         'status' => $this->FailedStatus,
        //         'message'    => 'No Collection center found to receive bailed items',
        //     ], 500);
        // }





        $from = Location::where('id', Auth::user()->location_id)->first()->name;
        $to = Location::where('id', $request->toLocation)->first()->name ?? "No Name";



        $transfer = new Transfer();
        $transfer->unsorted = $request->unsorted ?? 0;
        $transfer->from_location = $from;
        $transfer->to_location = $to;
        $transfer->user_id = Auth::id();
        $transfer->status = 0;
        $transfer->save();


    
        $chk_collection_center = CollectedBailedDetails::where('location_id', $request->toLocation)->first()->collected ?? null;
        if($chk_collection_center == null){

            $c = new CollectedBailedDetails();
            $c->location_id = $request->toLocation;
            $c->collected = $request->unsorted;
            $c->user_id = Auth::user()->id;
            $c->save();
        }


       CollectedBailedDetails::where('location_id', Auth::user()->location_id)->decrement('collected', $request->unsorted);
       CollectedBailedDetails::where('location_id', $request->toLocation)->increment('collected', $request->unsorted);



        $notification_id = User::where('location_id', $request->toLocation)
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
                                "body" => "Incomming Transfer from "
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









}
