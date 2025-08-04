<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\UserProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    public function GetProperty()
    {
        $data = Property::where('status', 1)->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);

    }


    public function SaveProperty(request $request){

        $property = new UserProperty();
        $property->property_id = $request->property_id;
        $property->long = $request->long;
        $property->lat = $request->lat;
        $property->address = $request->address;
        $property->user_id = Auth::user()->id;
        $property->status = 1;
        $property->save();

        return response()->json([
            'status' => true,
            'data' => "Property has been created Successfully"
        ]);

    }


    public function GetPropertyByUserID(request $request){

        $data = UserProperty::where('user_id', Auth::id())->first();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);

    }


}
