<?php

namespace App\Http\Controllers;

use App\Events\AvailableDrivers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\Authenticate;


class BroadcastAvailableDrivers extends Controller
{

    public function broadcastAvailableDrivers(request $request)
    {

        $lat = $request->input('lat');
        $lng = $request->input('lng');

        if (!$lat || !$lng) {
            return response()->json([
                'status' => false,
                'message' => 'Missing latitude or longitude'
            ], 400);
        }

        $radius = 0.5;
        $users = User::where('online', 1)->selectRaw("
                *, (
                    6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(latitude))
                    )
                ) AS distance
            ", [$lat, $lng, $lat])
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->get('first_name', 'last_name', 'latitude', 'longitude','phone');

        return response()->json([
            'status' => true,
            'data' => $users
        ]);


    }



}
