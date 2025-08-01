<?php

namespace App\Http\Controllers;

use App\Services\MicrosoftGraphMailService;
use Ramsey\Uuid\Uuid;
use Response;
use Carbon\Carbon;
use FFI\Exception;
use App\Models\User;
use App\Models\Slider;
use App\Models\Greeting;
use App\Models\UserRole;
use App\Models\AccessToken;
use App\Models\AgentRequest;
use Illuminate\Http\Request;
//use Tymon\JwtAuth\Facades\JwtAuth;
use App\Models\AccountRequest;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthCoontroller extends Controller
{
    public $successStatus = true;
    public $FailedStatus = false;

    public function Login()
    {

        $email_code = random_int(100000, 999999);

        try {
            //Login to account

            $credentials = request(['email', 'password']);

            Passport::tokensExpireIn(Carbon::now()->addMinutes(15));
            Passport::refreshTokensExpireIn(Carbon::now()->addMinutes(15));

            if (!auth()->attempt($credentials)) {
                return response()->json([
                    'status' => $this->FailedStatus,
                    'message' => 'Invalid email or password',
                ], 500);
            }

            $token = auth()->user()->createToken('API Token')->accessToken;

            $slider = Slider::all();

            if (Auth::user()->uuid == null) {
                $uuid = Uuid::uuid4()->toString();
                User::where('id', Auth::id())->update(['uuid' => $uuid]);
            }

            return response()->json([
                "status" => $this->successStatus,
                'message' => "login Successfully",
                'user' => auth()->user()->load(['location']),
                'role' => auth()->user()->role->name,
                'token' => $token,
                'slider' => $slider,
            ], 200);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => 'Error',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function resend_code(Request $request, MicrosoftGraphMailService $mailer)
    {
        try {
            $email_code = random_int(100000, 999999);

            $email = $request->email;

            $check_email = User::where('email', $email)
                ->first()->email ?? null;

            if ($check_email == null) {
                $save = new User();
                $save->email = $email;
                $save->save();
            }

            $check_is_email_verified = User::where('email', $email)
                ->first()->is_email_verified;

            $check_email = User::where('email', $email)
                ->first()->email;

            if ($check_is_email_verified == 1) {

                return response()->json([

                    'status' => $this->FailedStatus,
                    'message' => 'Email Already Exist',

                ], 500);
            }

            $update = User::where('email', $email)
                ->update(['email_code' => $email_code]);

            $greeting = Greeting::where('gender', 'both')
                ->first()->title;


            $Data = [
                'fromsender' => 'info@kaltani.com', 'TRASHBASH',
                'toreceiver' => $email,
                'email_code' => $email_code,
                'greeting' => $greeting,
            ];

            $subject = "Verification Code";
            $view = 'verify-code';

            $mailer->SendEmailView($email, $subject, $view, $Data);


            return response()->json([
                'status' => $this->successStatus,
                'message' => 'Code sent successfully',
            ], 200);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function verify_email(Request $request)
    {

        try {
            $email_code = $request->email_code;
            $email = $request->email;

            $get_email_code = User::where('email', $email)
                ->first()->email_code;

            if ($email_code == $get_email_code) {

                return response()->json([

                    'status' => $this->successStatus,
                    'message' => 'Email has been successfully Verified',

                ], 200);

            }

            return response()->json([

                'status' => $this->FailedStatus,
                'message' => 'Invalid Code',

            ], 200);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function update_email(Request $request)
    {

        try {
            $old_email = $request->old_email;

            $new_email = $request->new_email;

            $update = User::where('email', $old_email)
                ->update(['email' => $new_email]);

            return response()->json([

                'status' => $this->successStatus,
                'message' => "Email has been successfully Updated to $new_email ",

            ], 200);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function sms_email_code(Request $request)
    {

        try {
            $phone = $request->phone;

            $update = User::where('id', Auth::id())
                ->update(['phone2' => $phone]);

            $phone2 = User::where('id', Auth::id())
                ->first()->phone2;

            $email_code = random_int(100000, 999999);

            $update = User::where('phone2', $phone)
                ->update(['email_code' => $email_code]);

            $verify = User::where('phone2', $phone)
                ->first()->is_phone_verified;

            if ($verify == 1) {

                return response()->json([

                    'status' => $this->FailedStatus,
                    'message' => "Phone number already verified",

                ], 500);

            }

            $api_key = env('TAPI');
            $sender_id = 'N-Alert';
            $curl = curl_init();
            $data = array(

                "api_key" => "$api_key",
                "to" => "+234$phone2",
                "from" => "$sender_id",
                "sms" => "Your Enkwave confirmation code is $email_code. Valid for 5 minutes, one-time use only",
                "type" => "plain",
                "channel" => "dnd",

            );

            $post_data = json_encode($data);

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.ng.termii.com/api/sms/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post_data,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $result = json_decode($response);

            $message = $result->message;

            if ($result->message == 'Successfully Sent') {

                return response()->json([

                    'status' => $this->successStatus,
                    'message' => "Code sent successfully",

                ], 200);

            }

            return response()->json([

                'status' => $this->FailedStatus,
                'message' => "Error!! $message",

            ], 500);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function verify_sms_code(Request $request)
    {

        try {

            $phone = $request->phone;
            $code = $request->code;

            $email_code = User::where('phone2', $phone)
                ->first()->email_code;

            if ($email_code == $code) {

                $update = User::where('phone2', $phone)
                    ->update(['is_phone_verified' => 1]);

                return response()->json([

                    'status' => $this->successStatus,
                    'message' => "Phone number successfully verified",

                ], 200);
            }

            return response()->json([

                'status' => $this->FailedStatus,
                'message' => "Invalid verification code",

            ], 500);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function pin_login(Request $request)
    {

        try {

            $login_pin = $request->login_pin;

            $user_pin = User::where('id', Auth::id())
                ->first()->pin;

            if ((Hash::check(request('login_pin'), $user_pin)) == false) {

                return response()->json([

                    'status' => $this->FailedStatus,
                    'message' => 'Invalid User Pin',

                ], 500);

            }

            $token = auth()->user()->createToken('API Token')->accessToken;

            $user = User::where('id', Auth::id())
                ->first();

            return response()->json([

                'status' => $this->successStatus,
                'user' => $user,
                'token' => $token,

            ], 200);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['status' => $this->successStatus, 'message' => 'Successfully logged out'], 200);
    }

    public function createNewToken($token)
    {

        return response()->json(
            [
                'status' => $this->successStatus,
                'expiresIn' => auth('api')->factory()->getTTL() * 60 * 60 * 3,
                'user' => auth()->user(),
                'tokenType' => 'Bearer',
                'accessToken' => $token,

            ], 200
        );

    }

    public function register(Request $request)
    {

        try {

            $user = User::create(array_merge(
            ));
            $token = $user->createToken('API Token')->accessToken;

            $deviceId = AccessToken::find(Auth::id());
            $deviceId->update(['device_id' => $request->device_id]);

            $update = User::where('email', $request->email)
                ->update(['email' => 1]);

            $update = User::where('email', $request->email)
                ->update(['is_email_verified' => 1]);

            return response()->json([
                'status' => $this->successStatus,
                'message' => 'User successfully registered',
                'user' => $user,
                'token' => $token,
            ], 200);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }
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
            'user' => auth()->user(),
        ], 200);
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
            $arr = array("status" => $this->FailedStatus, "message" => $validator->errors()->first());
        } else {
            try {
                if ((Hash::check(request('old_password'), $users->password)) == false) {
                    $arr = array("status" => $this->FailedStatus, "message" => "Check your old password.");
                } else if ((Hash::check(request('new_password'), $users->password)) == true) {
                    $arr = array("status" => $this->FailedStatus, "message" => "Please enter a password which is not similar then current password.");
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
                $arr = array("status" => $this->FailedStatus, "message" => $msg);
            }
        }
        return \Response::json($arr);
    }

    public function updatePin(Request $request)
    {

        $input = $request->all();
        $userid = Auth::guard('api')->user()->id;
        //dd($userid);
        $users = User::find($userid);
        $rules = array(
            'old_pin' => 'required',
            'new_pin' => 'required|min:4',
            'confirm_pin' => 'required|same:new_pin',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => $this->FailedStatus, "message" => $validator->errors()->first());
        } else {
            try {
                if ((Hash::check(request('old_pin'), $users->pin)) == false) {
                    $arr = array("status" => $this->FailedStatus, "message" => "Check your old pin.");
                } else if ((Hash::check(request('new_pin'), $users->pin)) == true) {
                    $arr = array("status" => $this->FailedStatus, "message" => "Please enter a pin which is not similar then current pin.");
                } else {
                    User::where('id', $userid)->update(['pin' => Hash::make($input['new_pin'])]);
                    $arr = array("status" => $this->successStatus, "message" => "Password updated successfully.");
                }
            } catch (Exception $e) {
                if (isset($e->errorInfo[2])) {
                    $msg = $e->errorInfo[2];
                } else {
                    $msg = $e->getMessage();
                }
                $arr = array("status" => $this->FailedStatus, "message" => $msg);
            }
        }
        return \Response::json($arr);
    }

    public function customer_register(Request $request, MicrosoftGraphMailService $mailer)
    {
        try {

            $first_name = $request->first_name;
            $last_name = $request->last_name;
            $phone = $request->phone;
            $address = $request->address;
            $last_name = $request->last_name;
            $lga = $request->lag;
            $email = $request->email;
            $state = $request->state;
            $gender = $request->gender;
            $account_name = $request->account_name;
            $account_number = $request->account_number;
            $pin = $request->pin;
            $dob = $request->dob;
            $age = $request->age;
            $bank_code = $request->bank_code;
            $bank_name = $request->bank_name;
            $password = $request->password;
            $email = $request->email;

            $getemail = User::where('email', $email)
                ->first()->email ?? null;

            if ($getemail == null) {

                return response()->json([
                    'status' => $this->FailedStatus,
                    'message' => 'User can not be be found on the system',

                ], 500);

            }

            $get_role_id = UserRole::where('name', 'customer')
                ->first();

            $update = User::where('email', $email)->update([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'address' => $address,
                'lga' => $lga,
                'state' => $state,
                'gender' => $gender,
                'dob' => $dob,
                'age' => $age,
                'wallet' => 0,
                'bank_name' => $bank_name,
                'account_number' => $account_number,
                'account_name' => $account_name,
                'bank_code' => $bank_code,
                'role_id' => $get_role_id->id,
                'is_email_verified' => 1,
                'pin' => bcrypt($pin),
                'user_type' => 'customer',
                'password' => bcrypt($password),

            ]);

            if ($gender == 'Male') {

                $greeting = Greeting::where('gender', 'Male')
                    ->first()->title;

            } else {

                $greeting = Greeting::where('gender', 'female')
                    ->first()->title;

            }


            $Data = [
                'fromsender' => 'info@kaltani.com', 'TRASHBASH',
                'greeting' => $greeting,
            ];

            $subject = "Account Creation";
            $view = 'welcome';

            $mailer->SendEmailView($email, $subject, $view, $Data);


            return response()->json([
                'status' => $this->successStatus,
                'message' => 'User Registration Successful',

            ], 200);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function updateAccountDetails(Request $request)
    {
        try {

            $input = $request->all();

            $account = new AccountRequest();
            $account->account_number = $request->account_number;
            $account->account_name = $request->account_name;
            $account->bank_name = $request->bank_name;
            $account->bank_code = $request->bank_code;
            $account->user_id = Auth::id();
            $account->save();

            return response()->json([
                'status' => $this->successStatus,
                'message' => 'Your request has been sent successfuly',
                'data' => $account,
            ], 200);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function agent_register(Request $request)
    {

        try {

            $input = $request->all();

            $first_name = Auth::user()->first_name;
            $last_name = Auth::user()->last_name;

            $input = new AgentRequest();
            $input->org_name = $request->org_name;
            $input->address = $request->address;
            $input->state = $request->state;
            $input->lga = $request->lga;
            $input->longitude = $request->longitude;
            $input->latitude = $request->latitude;
            $input->city = $request->city;
            $input->user_id = Auth::id();
            $input->customer_name = $first_name . " " . $last_name;
            $input->phone = $request->phone;

            if ($file = $request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = $file->getClientOriginalName();
                $destinationPath = public_path() . 'upload/agent';
                $request->image->move(public_path('upload/agent'), $fileName);
                $input->image = $fileName;
            }

            $input->save();

            return response()->json([
                'status' => $this->successStatus,
                'message' => 'Your request to become an agent has been successful. A member of TRASHBASH team will get back to you shortly',
                'data' => $input,
            ], 200);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function agent_status(Request $request)
    {

        try {

            $user_id = Auth::id();
            $check_status = AgentRequest::where('user_id', $user_id)
                ->first();

            $status = $check_status->status;

            return response()->json([
                'status' => $this->successStatus,
                'agent' => $status,
            ], 200);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }

    }
    public function get_user(Request $request)
    {

        try {

            $user_id = Auth::id();

            $slider = Slider::all();

            $result = User::where('id', $user_id)
                ->first();

            $token = $request->bearerToken();

            return response()->json([
                'status' => $this->successStatus,
                'role' => auth()->user()->role->name,
                'user' => auth()->user()->load(['location']),
                'slider' => $slider,
                'token' => $token,
            ]);

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function forgot_password(Request $request, MicrosoftGraphMailService $mailer)
    {

        try {

            $email = $request->email;

            $check = User::where('email', $email)
                ->first()->email ?? null;

            $first_name = User::where('email', $email)
                ->first()->first_name ?? null;

            if ($check == $email) {

                $Data = [
                    'fromsender' => 'info@kaltani.com', 'TRASHBASH',
                    'first_name' => $first_name,
                    'link' => url('') . "/forgot_password/?email=$email",
                ];

                $subject = "Reset Password";
                $view = 'emaillink';

                $mailer->SendEmailView($email, $subject, $view, $Data);


                return response()->json([
                    'status' => $this->successStatus,
                    'message' => 'Check your email for instructions',
                ], 200);

            } else {

                return response()->json([

                    'status' => $this->FailedStatus,
                    'message' => 'User not found on our system',

                ], 500);

            }

        } catch (\Exception$e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function forgot_pin(Request $request, MicrosoftGraphMailService $mailer)
    {

        try{

        $email = $request->email;

        $check = User::where('email', $email)
            ->first()->email ?? null;

        $first_name = User::where('email', $email)
            ->first()->first_name ?? null;

        if ($check == $email) {


            $Data = [
                'fromsender' => 'info@kaltani.com', 'TRASHBASH',
                'first_name' => $first_name,
                'link' => url('') . "/forgot_pin/?email=$email",
            ];

            $subject = "Reset Pin";
            $view = 'pinlink';

            $mailer->SendEmailView($email, $subject, $view, $Data);


            return response()->json([
                'status' => $this->successStatus,
                'message' => 'Check your email for instructions',
            ], 200);

        } else {

            return response()->json([

                'status' => $this->FailedStatus,
                'message' => 'User not found on our system',

            ], 500);

        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => $this->FailedStatus,
            'message' => $e->getMessage(),
        ], 500);
    }

    }

}
