<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales;
use App\Models\FactoryTotal;
use App\Models\Transfer;
use App\Models\History;
use App\Models\BailedDetails;
use App\Models\SortDetails;
use App\Models\SalesDetails;
use App\Models\Total;
use App\Models\Factory;
use App\Models\BailingItem;
use App\Models\User;
use App\Models\Location;
use App\Models\TransferDetails;
use App\Models\Item;
use Auth;
use Carbon\Carbon;
use DB;
use App\Http\Traits\HistoryTrait;
use Illuminate\Support\Facades\Http;

class SalesController extends Controller
{
    //
    public $successStatus = true;
    public $FailedStatus = false;

    public function getSales(Request $request)
    {
        $transfer_item = BailingItem::all();
        $location = Location::all();



        return response()->json([
            "status" => $this->successStatus,
            "location" => $location,
            "transfer_item" => $transfer_item,
        ],200);

    }


    public function getSalesbrakedown(Request $request)
    {

        $location_id = $request->location_id;
       // $bailed_details = BailedDetails::where('location_id', $location_id)->get();
        $bailed_details = DB::table('bailed_details')->where('location_id', $location_id)->first();
        $bailing_items = BailingItem::all();

        return response()->json([
            "status" => $this->successStatus,
            "bailed_breakdown" => $bailed_details,
             "bailing_items" => $bailing_items,
        ],200);




        // $bailed_details = BailedDetails::where('location_id',Auth::user()->location_id)->first();
        // $transfer_item = BailingItem::all();
        // $location = Location::all();


        return response()->json([
            "status" => $this->successStatus,
            "bailed_breakdown" => $bailed_details,
            "location" => $location,
            "transfer_item" => $transfer_item,
        ],200);

    }






    public function sales(Request $request){
        try{
            $sales = new Sales();
        $sales->item_weight = $request->item_weight ?? 0;
        $sales->customer_name = $request->customer_name;
        $sales->price_per_ton = $request->price_per_ton ?? 0;
        $sales->freight = $request->freight ?? 0;
        if ($request->currency == "NGN") {
            $sales->amount_ngn = $request->amount ?? 0;
        }
        if($request->currency == "USD"){
            $sales->amount_usd = $request->amount ?? 0;
        }
        $sales->currency = $request->currency;
        $sales->customer_name = $request->customer_name;
        $sales->factory_id = Auth::user()->location_id;
        $sales->user_id = Auth::id();
        $sales->location_id = Auth::user()->location_id;
        $sales->save();

        $sale = $request->amount;

        $sales_amount = $request->amount ?? 0;
        $weight = $request->item_weight ?? 0;
        $recycled = $request->item_weight_output ?? 0;

        $factory_id = Auth::user()->location_id;

        if (FactoryTotal::where('factory_id',$factory_id)->exists()) {
            # code...
            $t = FactoryTotal::where('factory_id',$factory_id)->first();
            $t->update(['sales' => ($t->sales + $sales_amount)]);
            $t->update(['recycled' => ($t->recycled - $weight)]);
        }else{
            $total = new  FactoryTotal();
            $total->sales = $sales_amount;
            $total->factory_id = Auth::user()->location_id;
            $total->save();
        }
        $t = FactoryTotal::where('factory_id',$factory_id)->first();

        return response()->json([
            "status" => $this->successStatus,
            "message" => "Sales record created Successful",
            "data" => $sales,
            "total" => $t->sales
        ],200);
        }catch (Exception $e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message'    => 'Error',
                'errors' => $e->getMessage(),
            ], 401);
        }


    }

    public function saleBailed(Request $request)
    {
            try{
            $result = (($request->Clean_Clear["total_weight"] ?? 0) + ($request->Others["total_weight"] ?? 0) + ($request->Green_Colour["total_weight"] ?? 0) + ($request->Trash["total_weight"] ?? 0));
                $t = BailedDetails::where('location_id', $request->collection_id)->first();
                if(empty($t)){
                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'No Record Found',
                    ], 500);

                }
                //dd($t->bailed);
                    $tbailed = $t->Clean_Clear + $t->Green_Colour + $t->Others + $t->Trash;
                    if($result > $tbailed){

                        return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Bailed',
                    ], 500);
                    }
                    $checkSort = BailedDetails::where('location_id', $request->collection_id)->first();
                    //dd($checkSort->Clean_Clear,($request->Clean_Clear["total_weight"]));
                 if (empty($checkSort)) {

                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'No Collection Found',
                    ], 500);
                 }
                 if (($request->Clean_Clear["total_weight"] ?? 0) > $checkSort->Clean_Clear) {


                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Clean Clear',
                    ], 500);

                }elseif (($request->Green_Colour["total_weight"] ?? 0) > $checkSort->Green_Colour) {

                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Green Colour',
                    ], 500);
                }elseif (($request->Others["total_weight"] ?? 0) > $checkSort->Others) {

                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Others',
                    ], 500);
                }elseif (($request->Trash["total_weight"] ?? 0) > $checkSort->Trash) {

                    return response()->json([
                        'status' => $this->FailedStatus,
                        'message'    => 'Insufficent Trash',
                    ], 500);
                }



                    $saledetails = new SalesDetails();
                    $saledetails->Clean_Clear = $request->Clean_Clear["total_weight"] ?? 0;
                    $saledetails->Green_Colour = $request->Green_Colour["total_weight"] ?? 0;
                    $saledetails->Others = $request->Others["total_weight"] ?? 0;
                    $saledetails->Trash = $request->Trash["total_weight"] ?? 0;
                    $saledetails->location_id = $request->collection_id;
                    $saledetails->customer_name = $request->customer_name;
                    $saledetails->price_per_ton = $request->price_per_ton ?? 0;

                    $saledetails->clean_clear_qty = $request-> Clean_Clear["quantity"]?? 0;
                    $saledetails->green_color_qty = $request->Green_Colour["quantity"]?? 0;
                    $saledetails->other_qty = $request->Others["quantity"] ?? 0;
                    $saledetails->trash_qty = $request->Trash["quantity"] ?? 0;

                    $saledetails->currency = $request->currency;
                    $saledetails->freight = $request->freight;
                    if ($request->currency == "NGN") {
                        $saledetails->amount_ngn = $request->amount ?? 0;
                    }
                    if($request->currency == "USD"){
                        $saledetails->amount_usd = $request->amount ?? 0;
                    }
                    $saledetails->user_id = Auth::id();
                    $saledetails->save();


                    // $saledetails = ($saledetails->Clean_Clear + $saledetails->Others + $saledetails->Green_Colour + $saledetails->Trash);
                    // $total = Total::where('location_id',$request->collection_id)->first();
                    // $old_total_transfered = $total->transfered;
                    // $total->update(['bailed' => ($total->bailed - $saledetails)]);

                    $updated = BailedDetails::where('location_id', $request->collection_id)->first();
                    //dd($updated);
                    $updated->decrement('Clean_Clear', ($request->Clean_Clear["total_weight"] ?? 0));
                    $updated->decrement('Green_Colour', ($request->Green_Colour["total_weight"] ?? 0));
                    $updated->decrement('Others' ,($request->Others["total_weight"] ?? 0));
                    $updated->decrement('Trash', ($request->Trash["total_weight"] ?? 0));

                    $factory_id = $request->collection_id;
                    $sales_amount = $request->amount ?? 0;
                    if (FactoryTotal::where('location_id',$factory_id)->exists()) {
                        # code...
                        $t = FactoryTotal::where('location_id',$factory_id)->first();
                        $t->update(['sales' => ($t->sales + $sales_amount)]);
                    }else{
                        $total = new  FactoryTotal();
                        $total->sales = $sales_amount;
                        $total->location_id = $request->collection_id;
                        $total->save();
                    }

            return response()->json([
            "status" => $this->successStatus,
            "message" => "Sales  Successful",
            "data" => $saledetails,
            "total" => $t->sales
        ],200);
            } catch (Exception $e) {
                return response()->json(["error"=>$e]);
            }
    }
}
