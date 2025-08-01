<?php

namespace App\Http\Controllers;

use App\Events\AvailableDrivers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\Authenticate;


class BroadcastAvailableDrivers extends Controller
{

    public function UpdateDriverStatus(Request $request)
    {

        if($request->availability === 1){
            User::where('id', Auth::id())->update(['online' => 1]);
            return response()->json([
                'status' => true,
                'message' => "Driver is now online"
            ]);

        }else{

            User::where('id', Auth::id())->update(['online' => 0]);
            return response()->json([
                'status' => true,
                'message' => "Driver is now offline"
            ]);

        }


    }


        public function DriverStatus(Request $request)
    {
        $status = User::where('id', Auth::id())->first()->online;
        if($status === 0){
            $availability = false;
        }else{
            $availability = true;
        }

        return response()->json([
            'status' => true,
            'availability' => $availability
        ]);


    }

        public function UpdateLocation(Request $request)
    {

        $lat = $request->input('lat');
        $lng = $request->input('lng');

        if (!$lat || !$lng) {
            return response()->json([
                'status' => false,
                'message' => 'Missing latitude or longitude'
            ], 400);
        }

        $users = User::where('id', Auth::id())->update([
            'longitude' => $lng,
            'latitude' => $lat
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Location updated successfully'
        ], 200);

    }

    public function broadcastAvailableDrivers(Request $request)
    {


        $lat = $request->input('lat');
        $lng = $request->input('lng');

        if (!isset($lat, $lng) || !is_numeric($lat) || !is_numeric($lng)) {
            return response()->json([
                'status' => false,
                'message' => 'Missing or invalid latitude or longitude'
            ], 400);
        }

        $lat = (float) $lat;
        $lng = (float) $lng;

        $radiusKm = 50;

        $users = User::where('online', 1)
            ->select('first_name', 'last_name', 'latitude', 'longitude', 'phone')
            ->selectRaw(
                "(6371 * acos(
            cos(radians(?)) * cos(radians(CAST(latitude AS DECIMAL(10,6)))) *
            cos(radians(CAST(longitude AS DECIMAL(10,6))) - radians(?)) +
            sin(radians(?)) * sin(radians(CAST(latitude AS DECIMAL(10,6))))
        )) AS distance",
                [$lat, $lng, $lat]
            )
            ->havingRaw('distance <= ?', [$radiusKm])
            ->orderBy('distance')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $users
        ]);


    }



}
