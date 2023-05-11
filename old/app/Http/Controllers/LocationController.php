<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\User;
use App\Models\Total;
use Illuminate\Http\Response;
use Auth;

class LocationController extends Controller
{
    //
    public $successStatus = true;
    public $FailedStatus = false;

    public function location(Request $request)
    {
        $location = new Location();
        $location->name = $request->input('name');
        $location->address = $request->input('address');
        $location->city = $request->input('city');
        $location->state = $request->input('state');
        $location->user_id = Auth::id();
        $location->save();

        $t = Location::latest()->first();
        //dd($t);
        $total = new Total();
        $total->locationId = $t->id;
        $total->save();

        return  response()->json([
            "status" => $this->successStatus,
            "message" => "Successful",
            "data" => $location
        ],200);
    }
    public function getLocation(Request $request)
    {
        //dd($user->location);
        $location = Location::where('type', 'f')->get();
        return  response()->json([
            "status" => $this->successStatus,
            "message" => "Successful",
            "data" => $location
        ],200);
    }
}
