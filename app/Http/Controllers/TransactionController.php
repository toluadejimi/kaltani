<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\Transaction;
use App\Models\User;
use Auth;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mail;

class TransactionController extends Controller
{
    //
    public $SuccessStatus = true;
    public $FailedStatus = false;

    public function get_rate(Request $request)
    {
        $rate = Rate::where('id', 1)
            ->first()->rate;
        $transfer_fee = Rate::where('id', 2)
            ->first()->rate;

        return response()->json([
            "status" => $this->SuccessStatus,
            "plastic_rate_per_kg" => $rate,
            'trasnfer_fee' => $transfer_fee,
        ], 200);

    }

    public function get_all_transactions(Request $request)
    {
        try {
            $user_id = Auth::user()->id;

            $result = Transaction::orderBy('id', 'DESC')
            ->where('user_id', $user_id)
            ->take(10)->get();

            return response()->json([
                "status" => $this->SuccessStatus,
                "message" => "Successful",
                "data" => $result,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'msg' => 'Error',
                'errors' => $e->getMessage(),
            ], 401);
        }

    }

    public function verify_bank_account(Request $request)
    {

        $account_number = $request->input('account_number');
        $bank_code = $request->input('bank_code');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://web.enkpay.com/api/resolve-bank?account_number=$account_number&bank_code=$bank_code",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $var = curl_exec($curl);
        curl_close($curl);
        $var = json_decode($var);


        if($var->status == 'success'){
            return response()->json([
                'status' => $this->SuccessStatus,
                'account_name' => $var->customer_name
            ], 200);
        }

        return response()->json(['status' => $this->FailedStatus, 'message' => 'Please check the bank selected or account number and try again.'], 200);



    }

    public function get_banks(Request $request)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://web.enkpay.com/api/get-banks',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
        ),
        ));


        $data = curl_exec($curl);
        curl_close($curl);

        $var = json_decode($data);

        return response()->json(['status' => $this->SuccessStatus, 'message' => $var], 200);

    }

    public function fetch_account(Request $request)
    {

        $account_number = $request->input('account_number');
        $bank_code = $request->input('bank_code');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://web.enkpay.com/api/resolve-bank?account_number=$account_number&bank_code=$bank_code",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $var = curl_exec($curl);
        curl_close($curl);
        $var = json_decode($var);

        if($var->status == 'success'){
            return response()->json([
                'status' => $this->SuccessStatus,
                'account_name' => $var->customer_name
            ], 200);
        }

        return response()->json(['status' => $this->FailedStatus, 'message' => 'Please check the bank selected or account number and try again.'], 200);


    }













    public function verify_pin(Request $request)
    {

        $transfer_pin = $request->input('transfer_pin');

        $getpin = Auth()->user();
        $user_pin = $getpin->pin;

        if (Hash::check($transfer_pin, $user_pin)) {

            return response()->json([
                "status" => $this->SuccessStatus,
                "message" => "Pin Confrimed",
            ], 200);
        } else {
            return response()->json([
                "status" => $this->FailedStatus,
                "message" => "Incorrect Pin, Please try again",
            ], 500);
        }

    }

    public function bank_transfer(Request $request)
    {

        $key = env('FLW_SECRET_KEY');

        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $account_number = Auth::user()->account_number;
        $account_bank = Auth::user()->bank_code;
        $amount = $request->amount;
        $narration = "Debit";
        $currency = "NGN";

        $user_wallet = Auth::user()->wallet;


        if ($amount > $user_wallet) {
            return response()->json([
                "status" => $this->FailedStatus,
                "message" => "Insufficient Balance",
            ], 500);

        }


        if ($amount <= 1000) {

            return response()->json([
                "status" => $this->FailedStatus,
                "message" => "You can not withdrwal less than NGN 1000",
            ], 500);

        }

        $databody = array(
            "account_number" => $account_number,
            "account_bank" => $account_bank,
            "amount" => $amount,
            "narration" => $narration,
            "currency" => $currency,

        );
















        $var = curl_exec($curl);
        curl_close($curl);

        $var = json_decode($var);

        dd($var);

        $message = $var->message;

        if ($var->status == 'success') {

               //create Debit transaction
               $transaction = new Transaction();
               $transaction->user_id = $user_id;
               $transaction->reference = $var->data->reference;
               $transaction->amount = $amount;
               $transaction->type = 'Debit';
               $transaction->user_type = $user_type;
               $transaction->trans_id = $var->data->id;
               $transaction->save();

               //update wallet
               $userwallet = Auth()->user();
               $useramount = $userwallet->wallet;
               $removemoney = (int) $useramount - (int) $amount;

               $update = User::where('id', $user_id)
                   ->update(['wallet' => $removemoney]);

               $receiveremail = Auth::user()->email;

               //send email
               $data = array(
                   'fromsender' => 'no-reply@kaltani.com', 'TRASH BASH',
                   'subject' => "Withdwral",
                   'toreceiver' => $receiveremail,
               );

               Mail::send('withdwral', $data, function ($message) use ($data) {
                   $message->from($data['fromsender']);
                   $message->to($data['toreceiver']);
                   $message->subject($data['subject']);

               });

            return response()->json([

                'status' => $this->SuccessStatus,
                'message' => 'Your transfer is processing',

            ], 200);
        }



            return response()->json([

                'status' => $this->FailedStatus,
                'message' => 'Error, try again later',

            ], 500);




    }

    public function transaction_verify(Request $request)
    {

        $user_id = Auth::user()->id;
        $id = $request->id;

        $key = env('FLW_SECRET_KEY');

        $databody = array(

        );

        $body = json_encode($databody);
        $curl = curl_init();

        $key = env('FLW_SECRET_KEY');
        //"Authorization: $key",
        curl_setopt($curl, CURLOPT_URL, "https://api.flutterwave.com/v3/transfers/$id");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            "Authorization: $key",
        )
        );

        $var = curl_exec($curl);
        curl_close($curl);

        $var = json_decode($var);

        if ($var->data->status == 'FAILED') {

            $userwallet = Auth()->user();
            $useramount = $userwallet->wallet;
            $refundmoney = (int) $useramount + (int) $var->data->amount;

            $update = User::where('id', $user_id)
                ->update(['wallet' => $refundmoney]);
        }

        return response()->json([
            "status" => $this->SuccessStatus,
            "message1" => $var,
            "message2" => "Please try again later",
        ], 200);

    }

}
