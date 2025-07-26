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


        User::where('id', Auth::id())->update(['longitude' => $request->log, 'latitude' => $request->lat]);

        $drivers = User::where('status', 1)->get(['id', 'first_name', 'last_name', 'latitude', 'longitude', 'phone']);
        if($drivers){
            return response()->json([
                'status' => true,
                'data' => $drivers
            ]);

        }


    }

}
