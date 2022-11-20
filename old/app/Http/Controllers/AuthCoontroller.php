<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
//use Tymon\JwtAuth\Facades\JwtAuth;
use Laravel\Passport\TokenRepository;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Carbon\Carbon;

class AuthCoontroller extends Controller
{
    public $successStatus = true;
    public $failedStatus = false;


    public function Login()
    {
        try {
            //Login to account

                $credentials = request(['email', 'password']);

                Passport::tokensExpireIn(Carbon::now()->addDays(3));
                Passport::refreshTokensExpireIn(Carbon::now()->addDays(3));

            if (! auth()->attempt($credentials)) {
                return response()->json([
                    'status'=>$this->failedStatus,
                    'message' => 'Invalid email or password'
                ], 500);
            }

            $token = auth()->user()->createToken('API Token')->accessToken;
    
    
            Auth::guard('api')->check();
    
        return response()->json([
            "status" => $this->successStatus,
            'message' => "login Successfully",
            'user' => auth()->user()->load(['location']),
            'role' => auth()->user()->role->name,
            'token' => $token,
            'expiresIn' => Auth::guard('api')->check(),
        ],200);
        } catch (Exception $e) {
            return response()->json([
                'status' => $this->failedStatus,
                'message'    => 'Error',
                'errors' => $e->getMessage(),
            ], 401);
        }
    }


    public function logout()
    {
        auth()->logout();

        return response()->json(['status' => $this->successStatus,'message' => 'Successfully logged out'],200);
    }

    public function createNewToken($token){
        return response()->json(
            [
                'status' => $this->successStatus,
                'expiresIn' => auth('api')->factory()->getTTL()*60*60*3,
                'user'=> auth()->user(),
                'tokenType' =>'Bearer',
                'accessToken' => $token,
                
            ],200
            );
       
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|string',
            'location' => 'required|string',
            'role' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
        ]);
        if($validator->fails()){
            return response()->json(['status' => $this->failedStatus,$validator->errors()->toJson()], 400);
        }
        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => Hash::make($request->password)]
                ));
                $token = $user->createToken('API Token')->accessToken;
        return response()->json([
            'status' => $this->successStatus,
            'message' => 'User successfully registered',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function refresh()
    {
        return $this->createToken(auth()->refresh());
    }

    public function deviceId(Request $request)
    {
        $deviceId = User::find(Auth::id());
        $deviceId->update(['device_id' => $request->device_id]);
        return response()->json([
            'status' => $this->successStatus,
            'message' => 'DeviceId Updated',
            'user'=> auth()->user(),
        ],200);
    }
    
     public function updateUser(Request $request)
    {
        $input = $request->all();
        $userid = Auth::guard('api')->user()->id;
        //dd($userid);
        $users = User::find($userid);
        $rules = array(
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => $this->failedStatus, "message" => $validator->errors()->first());
        } else {
            try {
                if ((Hash::check(request('old_password'), $users->password)) == false) {
                    $arr = array("status" => $this->failedStatus, "message" => "Check your old password." );
                } else if ((Hash::check(request('new_password'), $users->password)) == true) {
                    $arr = array("status" => $this->failedStatus, "message" => "Please enter a password which is not similar then current password.");
                } else {
                    User::where('id', $userid)->update(['password' => Hash::make($input['new_password'])]);
                    $arr = array("status" => $this->successStatus, "message" => "Password updated successfully.");
                }
            } catch (Exception $e) {
                if (isset($e->errorInfo[2])) {
                    $msg = $e->errorInfo[2];
                } else {
                    $msg = $e->getMessage();
                }
                $arr = array("status" => $this->failedStatus, "message" => $msg);
            }
        }
        return \Response::json($arr);
    }
}
