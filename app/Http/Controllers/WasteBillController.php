<?php

namespace App\Http\Controllers;

use App\Models\BulkDrop;
use App\Models\Greeting;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WasteBill;
use App\Services\MicrosoftGraphMailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WasteBillController extends Controller
{
    public function GetBill()
    {

        $bills = WasteBill::where('user_id', Auth::id())->get();
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
        if ($user) {


            $bill = WasteBill::where('user_id', $user->id)->get();
            $usr['fullname'] = $user->first_name . ' ' . $user->last_name;
            $usr['customer_id'] = $user->customer_id;
            $usr['address'] = $user->address;

            if ($user->status == 0) {
                $usr['status'] = "Inactive";
            } else {
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

        $trx_ref = Transaction::where('trans_id', $request->order_id)->where('status', 0)->first();
        if ($trx_ref) {

            WasteBill::where('ref', $trx_ref->trans_id)->update(['status' => 1]);
            $trx->update(['status' => 1]);
            $user = User::where('id', $trx_ref->user_id)->first();

            $due_date = WasteBill::where('ref', $trx_ref->trans_id)->first()->due_date;

            $data = url('') . '/verify-invoice/' . $trx_ref->trans_id;

            $qrCode = base64_encode(
                QrCode::format('png')->size(120)->generate($data)
            );


            $invoiceData = [
                'customer_id' => $user->customer_id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'phone' => $user->phone,
                'description' => 'Monthly Waste Collection',
                'unit_price' => $trx_ref->amount,
                'total' => $trx_ref->amount,
                'subtotal' => $trx_ref->amount,
                'total_due' => $trx_ref->amount,
                'status' => 'PAID',
                'due_date' => $due_date,
                'qr_code' => $qrCode,
                'payment_method' => 'Bank Transfer',
            ];

            $mailer->sendEmail($user->email, 'Trash Bash Invoice', $invoiceData);

            return view('payment-completed', compact('trx_ref'));



        }




    }


    public function PayWasteBill(Request $request, MicrosoftGraphMailService $mailer)
    {


        if ($request->wallet == true) {

            $get_user = User::where('id', Auth::id())->first();
            if ($get_user->wallet >= $request->amount) {

                $due_date = WasteBill::where('ref', $request->ref)->first()->due_date;
                $trx = Transaction::where('trans_id', $request->ref)->first();
                $data = url('') . '/verify-invoice/' . $request->ref;
                $trx->update(['status' => 1]);


                $qrCode = base64_encode(
                    QrCode::format('png')->size(120)->generate($data)
                );


                $status = "PAID";

                $invoiceData = [
                    'customer_id' => $get_user->customer_id,
                    'name' => $get_user->first_name . ' ' . $get_user->last_name,
                    'phone' => $get_user->phone,
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

                $mailer->sendEmail($get_user->email, 'Trash Bash Invoice', $invoiceData);


                return response()->json([
                    'status' => true,
                    'message' => "Payment Successful",

                ]);


            }

            $amount_to_charge = $get_user->wallet - $request->amount;
            $api_key = Setting::where('id', 1)->first()->enkpay_key;
            $databody = array(
                'amount' => $amount_to_charge,
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
                'amount' => $amount_to_charge,
                'trans_id' => $request->ref,
                'account_no' => $var->account_no,
                'type' => "Monthly Bill Payment",
            ]);


            return response()->json([
                'status' => true,
                'data' => $var,
            ]);


        }


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
    public function PayWasteBillWeb(Request $request, MicrosoftGraphMailService $mailer)
    {


        Transaction::create([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'trans_id' => $request->ref,
            'account_no' => " ",
            'type' => "Monthly Bill Payment",
        ]);


        $api_key = Setting::where('id', 1)->first()->enkpay_key;
        $email = $request->email;
        $url = "https://web.sprintpay.online/pay?amount=$request->amount&key=$api_key&ref=$request->ref&email=$email";



        return redirect()->away($url);

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


    public function DriverProperties(request $request)
    {

        $driver = User::where('id', Auth::id())->first();
        $routes = $driver->routes;
        $items = Product::where('status', 1)->get()->makeHidden(['created_at', 'updated_at', 'status']);

        $columns = Schema::getColumnListing('waste_collection');
        $itemColumns = array_diff($columns, ['id', 'user_id', 'created_at', 'updated_at']);

        if (empty($itemColumns)) {
            $collected = 0;
        } else {
            $selectRaw = collect($itemColumns)->map(function ($col) {
                return "SUM(`$col`) as {$col}_total";
            })->implode(', ');

            $collected = DB::table('waste_collection')->selectRaw($selectRaw)->first();

        }

        $list = BulkDrop::where('status', 0)->get();

        $fixedJson = str_replace(['{', '}', '"'], ['[', ']', '"'], $routes);
        $fixedJson = preg_replace('/([a-zA-Z0-9_]+)(?=\s*,|\s*\])/', '"$1"', $fixedJson);
        $routesArray = json_decode($fixedJson, true);

        return response()->json([
            'status' => true,
            'route' => $routesArray,
            'items' => $items,
            'collected' => $collected,
            'bulk_list' => $list,
        ]);


    }


    public function GetBulkList(request $request)
    {

        $list = BulkDrop::where('status', 0)->get();

        return response()->json([
            'status' => true,
            'bulk_list' => $list,
        ]);

    }


    public function CustomerValidation(request $request)
    {
        $customer = User::where('phone', $request->phone)->first();
        if ($customer) {
            return response()->json([
                'status' => true,
                'name' => $customer->first_name . " " . $customer->last_name,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => "User not found"
        ]);

    }


    public function CustomerBulkDrop(Request $request, MicrosoftGraphMailService $mailer)
    {
        if ($request->has('item')) {
            $raw = $request->input('item');
            if (is_array($raw) && isset($raw[0]) && is_string($raw[0])) {
                $decoded = json_decode($raw[0], true);
                $request->merge(['items' => $decoded]);
            }
        }

        if (is_string($request->input('items'))) {
            $decoded = json_decode($request->input('items'), true);
            $request->merge(['items' => $decoded]);
        }



        $userId = Auth::id();
        $items = $request->input('items');
        $ref = "DRP" . random_int(0, 99999999);


        $savedFileUrls = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                if (!$file->isValid()) {
                    return response()->json([
                        'status' => false,
                        'message' => "File at index {$index} failed: " . $file->getErrorMessage(),
                    ], 422);
                }

                $filename = Str::uuid() . '_' . $file->getClientOriginalName();
                try {
                    $path = $file->storeAs("bulk_drop_files/{$userId}", $filename, 'public');
                } catch (\League\Flysystem\UnableToCreateDirectory $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Storage directory could not be created. Check permissions and disk config.',
                        'error' => $e->getMessage(),
                    ], 500);
                }
                $url = Storage::disk('public')->url($path);
                $savedFileUrls[] = $url;
            }
        }

        $request->validate([
            'long' => 'required|numeric',
            'lat' => 'required|numeric',
            'items' => 'required|array',
            'items.*.item' => 'required|string',
            'items.*.kg' => 'required|numeric|min:0',
            'files' => 'sometimes|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf,docx|max:5120',
        ]);





        BulkDrop::insert([
            'user_id' => $userId,
            'long' => $request->input('long'),
            'lat' => $request->input('lat'),
            'items' => json_encode($items),
            'address' => Auth::user()->address,
            'ref' => $ref,
            'images' => json_encode($savedFileUrls),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Flatten and update waste_collections
        $flatItems = [];
        foreach ($items as $entry) {
            $column = strtolower($entry['item']);
            $kg = $entry['kg'];
            $flatItems[$column] = $kg;

            if (!Schema::hasColumn('waste_collections', $column)) {
                Schema::table('waste_collections', function (Blueprint $table) use ($column) {
                    $table->float($column)->default(0)->nullable();
                });
            }
        }

        $row = DB::table('waste_collections')->where('user_id', $userId)->first();
        if ($row) {
            foreach ($flatItems as $column => $value) {
                DB::table('waste_collections')
                    ->where('user_id', $userId)
                    ->update([
                        $column => DB::raw("COALESCE($column, 0) + {$value}")
                    ]);
            }
        } else {
            $insertData = ['user_id' => $userId];
            foreach ($flatItems as $column => $value) {
                $insertData[$column] = $value;
            }
            DB::table('waste_collections')->insert($insertData);
        }

        // Greeting logic fixed (comparison, not assignment)
        if (Auth::user()->gender === 'Male') {
            $greeting = Greeting::where('gender', 'Male')->first()?->title ?? '';
        } else {
            $greeting = Greeting::where('gender', 'Female')->first()?->title ?? '';
        }

        $first_name = Auth::user()->first_name;
        $email = Auth::user()->email;

        $Data = [
            'fromsender' => 'info@kaltani.com',
            'first_name' => $first_name,
            'greeting' => $greeting,
            'order_id' => $ref,
        ];

        $subject = "New Drop Off";
        $view = 'dropoff';
        $mailer->SendEmailView($email, $subject, $view, $Data);

        return response()->json([
            'status' => true,
            'message' => "Drop off successful",
        ]);
    }



    public
    function GetCustomerOrder(request $request)
    {
        $orders = BulkDrop::where('user_id', Auth::id())->get();

        return response()->json([
                'status' => true,
                'data' => $orders,
            ]);


    }

    public function GetList(request $request)
    {

        $product = Product::all()->makeHidden(['created_at', 'updated_at']);
        return response()->json([
                'status' => true,
                'message' => $product,
        ]);

    }


    public function GetCustomerName(request $request)
    {

        $request->validate([
            'userId' => 'required',
        ]);

        $user = User::where('id', $request->userId)->first();
        if($user){
            $name = $user->first_name." ".$user->last_name;

            return response()->json([
                'status' => true,
                'name' => $name,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => "No user found",
        ]);

    }




    public function UpdateBulkDrop(Request $request)
    {
        if ($request->has('item')) {
            $raw = $request->input('item');
            if (is_array($raw) && isset($raw[0]) && is_string($raw[0])) {
                $decoded = json_decode($raw[0], true);
                $request->merge(['items' => $decoded]);
            }
        }

        if (is_string($request->input('items'))) {
            $decoded = json_decode($request->input('items'), true);
            $request->merge(['items' => $decoded]);
        }

        $request->validate([
            'long' => 'required|numeric',
            'lat' => 'required|numeric',
            'items' => 'required|array',
            'items.*.item' => 'required|string',
            'items.*.kg' => 'required|numeric|min:0',
            'files' => 'sometimes|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf,docx|max:5120',
            'id' => 'sometimes|exists:bulk_drops,id',
        ]);

        $items = $request->input('items');
        $ref = "DRP" . random_int(0, 99999999);

        $savedFileUrls = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                if (!$file->isValid()) {
                    return response()->json([
                        'status' => false,
                        'message' => "File at index {$index} failed: " . $file->getErrorMessage(),
                    ], 422);
                }

                $filename = Str::uuid() . '_' . $file->getClientOriginalName();
                try {
                    $path = $file->storeAs("bulk_drop_files/{$userId}", $filename, 'public');
                } catch (\League\Flysystem\UnableToCreateDirectory $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Storage directory could not be created. Check permissions and disk config.',
                        'error' => $e->getMessage(),
                    ], 500);
                }
                $url = Storage::disk('public')->url($path);
                $savedFileUrls[] = $url;
            }
        }

        DB::beginTransaction();
        try {
            $bulkDropData = [
                'user_id' => $userId,
                'items' => json_encode($items),
                'address' => Auth::user()->address,
                'updated_at' => now(),
            ];

            if (!empty($savedFileUrls)) {
                $bulkDropData['images'] = json_encode($savedFileUrls);
            }

            if ($request->filled('id')) {
                $bulkDrop = BulkDrop::where('id', $request->input('id'))
                    ->where('user_id', $userId)
                    ->firstOrFail();

                // If ref was null in $bulkDropData, preserve existing ref
                if (isset($bulkDropData['ref']) && $bulkDropData['ref'] === null) {
                    unset($bulkDropData['ref']);
                }

                $bulkDrop->update($bulkDropData);
                $refToUse = $bulkDrop->ref;
            } else {
                // Insert new
                $bulkDropData['ref'] = $ref;
                $bulkDropData['created_at'] = now();
                $bulkDrop = BulkDrop::create($bulkDropData);
                $refToUse = $ref;
            }

            // Flatten and update waste_collections
            $flatItems = [];
            foreach ($items as $entry) {
                $column = strtolower($entry['item']);
                $kg = $entry['kg'];
                $flatItems[$column] = $kg;

                if (!Schema::hasColumn('waste_collections', $column)) {
                    Schema::table('waste_collections', function (Blueprint $table) use ($column) {
                        $table->float($column)->default(0)->nullable();
                    });
                }
            }

            $existing = DB::table('waste_collections')->where('user_id', $userId)->first();
            if ($existing) {
                foreach ($flatItems as $column => $value) {
                    DB::table('waste_collections')
                        ->where('user_id', $userId)
                        ->update([
                            $column => DB::raw("COALESCE($column, 0) + {$value}")
                        ]);
                }
            } else {
                $insertData = ['user_id' => $userId];
                foreach ($flatItems as $column => $value) {
                    $insertData[$column] = $value;
                }
                DB::table('waste_collections')->insert($insertData);
            }

            // Greeting logic
            if (Auth::user()->gender === 'Male') {
                $greeting = Greeting::where('gender', 'Male')->first()?->title ?? '';
            } else {
                $greeting = Greeting::where('gender', 'Female')->first()?->title ?? '';
            }

            $first_name = Auth::user()->first_name;
            $email = Auth::user()->email;

            $Data = [
                'fromsender' => 'info@kaltani.com',
                'first_name' => $first_name,
                'greeting' => $greeting,
                'order_id' => $refToUse,
            ];

            $subject = "New Drop Off";
            $view = 'dropoff';
            $mailer->SendEmailView($email, $subject, $view, $Data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Update successful",
                'ref' => $refToUse,
                'bulk_drop_id' => $bulkDrop->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to save bulk drop: ' . $e->getMessage(),
            ], 500);
        }
    }

}
