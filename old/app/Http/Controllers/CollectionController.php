<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Collection;
use App\Models\CollectedDetails;
use Illuminate\Http\Response;
use Auth;

class CollectionController extends Controller
{
    //
    public $SuccessStatus = true;
    public $FailedStatus = false;

    public function collect(Request $request)
    {

        $t = CollectedDetails::where('location_id',Auth::user()->location_id)->first();

        if (empty($t) ){
            $create = new CollectedDetails();
            $create->location_id = Auth::user()->location_id;
            $create->collected = 0;
            $create->user_id = Auth::id();
            $create->save();
        }

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

        $collected = (int)$request->input('item_weight') ?? 0;
            $locationId = Auth::user()->location_id;

            $t = CollectedDetails::where('location_id',$locationId)->first();

            if (!empty($t) ) {
                $t->update(['collected' => ($t->collected + $collected)]);
            }


        return response()->json([
            "status" => $this->SuccessStatus,
            "message" => "Collection created Successful",
            "data" => $collect,
            "total" => $t->collected
        ],200);
    }



    public function getCollection(Request $request)
    {
        try{
            $collect = Collection::with('location','item')->where('location_id', Auth::user()->location_id)->get();
        return response()->json([
            "status" => $this->SuccessStatus,
            "message" => "Successful",
            "data" => $collect
        ],200);
        }catch (Exception $e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'msg'    => 'Error',
                'errors' => $e->getMessage(),
            ], 401);
        }

    }

}
