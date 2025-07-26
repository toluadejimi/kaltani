<?php

namespace App\Http\Controllers;

use App\Events\AvailableDrivers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastAvailableDrivers extends Controller
{

    public function broadcastAvailableDrivers(request $request)
    {


        User::where('id', Auth::id())->update(['longitude' => $request->log, 'latitude' => $request->lat]);

        $drivers = User::where('status', 1)->get(['id', 'first_name', 'last_name', 'latitude', 'longitude', 'status']);
        if($drivers){
            broadcast(new AvailableDrivers($drivers))->toOthers();

        }

        return response()->json([
            'status' => true,
            'message' => 'Available drivers broadcast'
        ]);
    }

}
