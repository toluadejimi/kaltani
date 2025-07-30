<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WasteBill;
use App\Services\MicrosoftGraphMailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WasteBillController extends Controller
{
    public function GetBill()
    {

        $bills = WasteBill::where('id', Auth::id())->get();
        return response()->json([
            'status' => true,
            'data' => $bills
        ]);

    }

    public function ScanCode(request $request)
    {

        $request->validate([
            'uuid' => 'required'
        ]);

        $user = User::where('uuid', $request->uuid)->first() ?? null;
        if($user){


            $bill = WasteBill::where('user_id', $user->id)->get();
            $usr['fullname'] = $user->first_name . ' ' . $user->last_name;
            $usr['customer_id'] = $user->customer_id;
            $usr['address'] = $user->address;

            if($user->status == 0){
                $usr['status'] = "Inactive";
            }else{
                $usr['status'] = "Active";
            }

            return response()->json([
                'status' => true,
                'bill_data' => $bill,
                'customer_info' => $usr

            ]);
        }

        return response()->json([
            'status' => false,
            'message' => "Customer not found",

        ], 422);


    }


    public function ProcessPaymentBill(Request $request, MicrosoftGraphMailService $mailer)
    {

        $trx = Transaction::where('account_no', $request->account_no)->where('status', 0)->first();
        if ($trx) {

            WasteBill::where('ref', $trx->trans_id)->update(['status' => 1]);
            $trx->update(['status' => 1]);
            $user = User::where('id', $trx->user_id)->first();

            $due_date = WasteBill::where('ref', $trx->trans_id)->first()->due_date;

            $data = url('') . '/verify-invoice/' . $trx->trans_id;

            $qrCode = base64_encode(
                QrCode::format('png')->size(120)->generate($data)
            );


            $invoiceData = [
                'customer_id' => $user->customer_id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'phone' => $user->phone,
                'description' => 'Monthly Waste Collection',
                'unit_price' => $trx->amount,
                'total' => $trx->amount,
                'subtotal' => $trx->amount,
                'total_due' => $trx->amount,
                'status' => 'PAID',
                'due_date' => $due_date,
                'qr_code' => $qrCode,
                'payment_method' => 'Bank Transfer',
            ];

            $mailer->sendEmail($user->email, 'Trash Bash Invoice', $invoiceData);


            return response()->json([
                'status' => true,
                'message' => "Payment Successful",

            ]);


        }


        $bills = WasteBill::where('id', Auth::id())->get();
        return response()->json([
            'status' => true,
            'data' => $bills
        ]);

    }


    public function PayWasteBill(Request $request)
    {

        $api_key = Setting::where('id', 1)->first()->enkpay_key;
        $databody = array(
            'amount' => $request->amount,
            'ref' => $request->ref,
            'email' => Auth::user()->email,
            'key' => $api_key,

        );

        $post_data = json_encode($databody);

        $email = Auth::user()->email;


        $url = "https://web.sprintpay.online/paynow?amount=$request->amount&key=$api_key&ref=$request->ref&email=$email&platform=kaltani";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => $post_data,
        ));

        $var2 = curl_exec($curl);
        curl_close($curl);
        $var = json_decode($var2);

        Transaction::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'trans_id' => $request->ref,
            'account_no' => $var->account_no,
            'type' => "Monthly Bill Payment",
        ]);


        return response()->json([
            'status' => true,
            'data' => $var,
        ]);

    }


    public function GetWasteBillPdf(Request $request)
    {


        $bill = WasteBill::where('ref', $request->ref)->first() ?? null;

        if ($bill) {

            $user = User::where('id', $bill->user_id)->first();

            $due_date = WasteBill::where('ref', $request->ref)->first()->due_date;

            $trx = Transaction::where('trans_id', $request->ref)->first();

            $data = url('') . '/verify-invoice/' . $request->ref;

            $qrCode = base64_encode(
                QrCode::format('png')->size(120)->generate($data)
            );


            if ($trx->status === 0) {
                $status = "UNPAID";
            } else {
                $status = "PAID";
            }

            $invoiceData = [
                'customer_id' => $user->customer_id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'phone' => $user->phone,
                'description' => 'Monthly Waste Collection',
                'unit_price' => $trx->amount,
                'total' => $trx->amount,
                'subtotal' => $trx->amount,
                'total_due' => $trx->amount,
                'status' => $status,
                'due_date' => $due_date,
                'qr_code' => $qrCode,
                'payment_method' => 'Bank Transfer',
            ];

            $pdf = Pdf::loadView('invoices.invoice', ['invoice' => $invoiceData]);

            $fileName = 'invoice_' . $user->customer_id . '_' . time() . '.pdf';
            return $pdf->download($fileName);
//
//            return response()->json([
//                'status' => true,
//                'invoice_url' => $pdfUrl
//            ]);


        }


        return response()->json([
            'status' => false,
            'message' => "Bill Not found"
        ], 422);


    }


}
