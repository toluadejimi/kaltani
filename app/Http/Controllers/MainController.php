<?php

namespace App\Http\Controllers;

use App\Http\Traits\HistoryTrait;
use App\Models\AgentRequest;
use App\Models\BailedDetails;
use App\Models\Bailing;
use App\Models\BailingItem;
use App\Models\CollectedDetails;
use App\Models\Collection;
use App\Models\DropOff;
use App\Models\Factory;
use App\Models\FactoryTotal;
use App\Models\History;
use App\Models\Item;
use App\Models\Location;
use App\Models\Rate;
use App\Models\Recycle;
use App\Models\RecyclesDetails;
use App\Models\Sales;
use App\Models\SalesDetails;
use App\Models\SortDetails;
use App\Models\SortedTransfer;
use App\Models\Sorting;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\TransferDetails;
use App\Models\User;
use App\Models\UserRole;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\Reject;
use Mail;
use Session;
use Termwind\Components\Raw;

class MainController extends Controller
{

    public $successStatus = true;
    public $FailedStatus = false;
    //
    use HistoryTrait;

    public function signin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {

            $user = User::where("id", Auth::id())->get();

            return redirect('dashboard')->with('message', 'Welcome');
        } else {
            return back()->with('error', 'Invalid Credentials');
        }

    }
    public function collect(Request $request)
    {

        $collect = new Collection();
        $collect->item_id = $request->input('item');
        $collect->created_at = $request->input('created_at');
        $collect->item_weight = $request->input('item_weight');
        $collect->price_per_kg = $request->input('price_per_kg');
        $collect->transport = $request->input('transport');
        $collect->loader = $request->input('loader');
        $collect->others = $request->input('others');
        $collect->location_id = $request->input('location');
        $collect->amount = $request->input('amount');
        $collect->user_id = Auth::id();
        $collect->save();

        $collected = $request->input('item_weight');
        $locationId = $request->input('location');

        $t = CollectedDetails::where('location_id', $locationId)->first();

        if (empty($t)) {
            $create = new CollectedDetails();
            $create->location_id = $locationId;
            $create->collected = $collected;
            $create->user_id = Auth::id();
            $create->save();
        } else {
            $t = CollectedDetails::where('location_id', $locationId)->increment('collected', $collected);

            //$t->increment('collected' ,$collected);

        }

        return back()->with('message', 'Collection Created Successfully');
    }
    public function viewCollect()
    {
        $item = Item::all();
        $center = Location::all();
        $collections = Collection::orderBy('created_at', 'desc')->get();

        return view('addCollection', compact('center', 'item', 'collections'));
    }

    public function update_password()
    {

        $user = User::all();

        return view('updatepassword', compact('user'));
    }

    public function updatepassword(Request $request)
    {
        $user = User::all();
        $input = $request->all();
        $userid = Auth::user()->id;
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
                    return back()->with('error', 'Check your old password');
                } else if ((Hash::check(request('new_password'), $users->password)) == true) {
                    return back()->with('error', 'Please enter a password which is not similar then current password');
                } else {
                    User::where('id', $userid)->update(['password' => Hash::make($input['new_password'])]);
                    return back()->with('message', 'Password Updated Successfully');
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
        return back()->with('error', $e);
        //return back()->with('message', 'Password Updated Successfully');
        //return view('updatepassword',compact('user','arr'));
    }

    public function logout()
    {
        Session::flush();

        Auth::logout();

        return redirect('/');
    }

    public function dashboard()
    {
        $users = User::select(\DB::raw("COUNT(*) as count"), DB::raw("MONTHNAME(created_at) as month_name"))
            ->whereYear('created_at', date('Y'))
            ->groupBy('month_name')
            ->orderBy('created_at', 'asc')
            ->pluck('count', 'month_name');

        $labels = $users->keys();
        $data = $users->values();
        $locations = Location::all()->count();
        $location = Location::all();
        // $totals = Total::all();

        $items = Item::all()->count();
        $collections = Collection::orderBy('created_at', 'DESC')
            ->paginate(10);

        $centers = Location::all()->count();
        $staffs = User::where('role_id', 2)->count();
        $salesusd = Sales::all()->sum('amount_usd');
        $salesngn = Sales::all()->sum('amount_ngn');
        $salesdetailsngn = SalesDetails::all()->sum('amount_ngn');
        $salesdetailsusd = SalesDetails::all()->sum('amount_usd');

        $usd = $salesusd + $salesdetailsusd;
        $ngn = $salesngn + $salesdetailsngn;

        $weightout = Recycle::all()->sum('item_weight_output');
        $users = User::all()->count();
        $factory = Factory::all()->count();

        return view('dashboard', compact('location', 'factory', 'locations', 'labels', 'collections', 'items', 'centers', 'staffs', 'weightout', 'users', 'usd', 'ngn'));
    }

    public function createUser(Request $request)
    {
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|string',
            'location_id' => 'required|string',
            'role_id' => 'required|string',
            'type' => 'nullable|string',
            'user_type' => 'required|string',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return back()->with('error', $validator->errors());
        }
        //dd($validator->validated());
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => Hash::make($request->password)]
        ));

        return back()->with('message', 'User Created Successfully');
    }

    public function user_edit($id)
    {
        $users = User::find($id);
        $collection = Location::all();
        $factory = Factory::all();
        $role = UserRole::all();
        return view('user_edit', compact('users', 'collection', 'factory', 'role'));
    }
    public function userDelete($id)
    {
        $users = User::find($id);
        $users->delete();
        return redirect('/users')->with('message', 'User Deleted Successfully');
    }

    public function agent_delete($id)
    {

        $agent = AgentRequest::find($id);
        $agent->delete();
        return back()->with('message', 'Agent Removed Successfully');

    }

    public function userEdit(Request $request, $id)
    {
        $user = User::find($id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->location_id = $request->location_id;
        $user->role_id = $request->role_id;
        $user->factory_id = $request->factory_id;
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect('/users')->with('message', 'User Updated Successfully');
    }
    public function users()
    {
        $users = User::where('user_type', 'staff')->get();
        $collection = Location::all();
        $factory = Factory::all();
        $roles = UserRole::all();

        return view('users', compact('users', 'collection', 'factory', 'roles'));
    }

    public function customers(Request $request)
    {
        $users = User::where('role_id', '18')
            ->get();
        $collection = Location::all();
        $factory = Factory::all();
        $roles = UserRole::all();

        return view('customers', compact('users', 'collection', 'factory', 'roles'));
    }

    public function agents(Request $request)
    {
        $users = AgentRequest::where('status', '1')
        ->get();
        $collection = Location::all();
        $factory = Factory::all();
        $roles = UserRole::all();

        return view('agents', compact('users', 'collection', 'factory', 'roles'));
    }


    public function agents_view(Request $request)
    {

        $user_id = $request->user_id;

        $users = User::where('user_type', 'agent')
        ->get();
        $collection = Location::all();
        $factory = Factory::all();
        $roles = UserRole::all();

        return view('agents', compact('users', 'collection', 'factory', 'roles'));
    }

    public function locations()
    {
        $location = Location::all();
        return view('locations', compact('location'));
    }
    public function factory()
    {
        $factory = Location::where('type', 'f')->get();
        return view('factory', compact('factory'));
    }

    public function collectionCenter()
    {
        $collectioncenter = Location::where('type', 'c')->get();
        //dd($collectioncenter->name);
        return view('collection_centers', compact('collectioncenter'));
    }

    public function viewfactory($id)
    {
        $factory = Location::where('id', $id)->first();
        //dd($factory);
        $transfer = Transfer::where('factory_id', $id)->get();
        $transferD = TransferDetails::where('location_id', $id)->first();
        $recycle = Recycle::where('factory_id', $id)->get();
        $input = Recycle::where('factory_id', $id)->sum('item_weight_input');
        $output = Recycle::where('factory_id', $id)->sum('item_weight_output');
        $soda = Recycle::where('factory_id', $id)->sum('costic_soda');
        $detergent = Recycle::where('factory_id', $id)->sum('detergent');
        return view('viewFactory', compact('factory', 'transfer', 'transferD', 'recycle', 'input', 'output', 'soda', 'detergent'));
    }

    public function factoryEdit($id)
    {
        $item = Factory::find($id);
        return view('factory_edit', compact('item'));
    }
    public function factoryUpdate(Request $request, $id)
    {
        $location = Factory::find($id);
        $location->name = $request->input('name');
        $location->address = $request->input('address');
        $location->city = $request->input('city');
        $location->state = $request->input('state');
        $location->user_id = Auth::id();
        $location->save();

        return redirect('/factory')->with('message', 'Updated Successfully');
    }
    public function factoryDelete($id)
    {
        $items = Factory::find($id);
        $items->delete();
        return redirect('/factory')->with('message', 'Deleted Successfully');
    }

    public function createItem(Request $request)
    {

        $items = new Item();
        $items->item = $request->input('item');
        $items->user_id = Auth::id();
        $items->save();

        return back()->with('message', 'Item Created Successfully');
    }
    public function createBailingItem(Request $request)
    {

        //dd(SortDetails::all());
        $items = new BailingItem();
        $items->item = $request->input('bailing_item');
        $items->items_id = $request->input('item_id');
        $items->user_id = Auth::id();
        $items->save();

        $data = BailingItem::all();
        $col = $request->input('bailing_item');

        if (Schema::hasColumn('sort_details', str_replace(" ", "_", $col))) {
            // do something
        } else {
            Schema::table('sort_details', function (Blueprint $table) use ($col) {
                $table->string(str_replace(" ", "_", $col))->default('0')->after('id');
            });
        }

        if (Schema::hasColumn('sortings', str_replace(" ", "_", $col))) {
            // do something
        } else {
            Schema::table('sortings', function (Blueprint $table) use ($col) {
                $table->string(str_replace(" ", "_", $col))->default('0')->after('id');
            });
        }

        if (Schema::hasColumn('sort_details_histories', str_replace(" ", "_", $col))) {
            // do something
        } else {
            Schema::table('sort_details_histories', function (Blueprint $table) use ($col) {
                $table->string(str_replace(" ", "_", $col))->default('0')->after('id');
            });
        }

        if (Schema::hasColumn('bailed_details', str_replace(" ", "_", $col))) {
            // do something
        } else {
            Schema::table('bailed_details', function (Blueprint $table) use ($col) {
                $table->string(str_replace(" ", "_", $col))->default('0')->after('id');
            });
        }

        if (Schema::hasColumn('bailed_details_histories', str_replace(" ", "_", $col))) {
            // do something
        } else {
            Schema::table('bailed_details_histories', function (Blueprint $table) use ($col) {
                $table->string(str_replace(" ", "_", $col))->default('0')->after('id');
            });
        }
        if (Schema::hasColumn('transfer_details_histories', str_replace(" ", "_", $col))) {
            // do something
        } else {
            Schema::table('transfer_details_histories', function (Blueprint $table) use ($col) {
                $table->string(str_replace(" ", "_", $col))->default('0')->after('id');
            });
        }
        if (Schema::hasColumn('transfer_details', str_replace(" ", "_", $col))) {
            // do something
        } else {
            Schema::table('transfer_details', function (Blueprint $table) use ($col) {
                $table->string(str_replace(" ", "_", $col))->default('0')->after('id');
            });
        }
        if (Schema::hasColumn('transfer_details', str_replace(" ", "_", $col))) {
            // do something
        } else {
            Schema::table('transfer_details', function (Blueprint $table) use ($col) {
                $table->string(str_replace(" ", "_", $col))->default('0')->after('id');
            });
        }

        return back()->with('message', 'Bailing Item Created Successfully');
    }

    public function bailingList()
    {
        $items = BailingItem::all();
        $mainItems = Item::all();
        return view('bailing_item', compact('items', 'mainItems'));
    }

    public function drop_offlist()
    {
        $money_out = DropOff::where('status', '1')->sum('amount');
        $pending_drop_off = DropOff::where('status', 0)->count();

        $total_weight = DropOff::where('status', '1')->sum('waste_weight');

        $total_unpaid = DropOff::where('status', '2')->count();

        $users = User::all();
        $dropofflist = DropOff::orderBy('created_at', 'DESC')
            ->paginate(15);

        //$dropofflist = Datatables::of(DropOff::query())->make(true);

        return view('dropofflist', compact('dropofflist', 'pending_drop_off', 'total_unpaid', 'money_out', 'total_weight'));

    }

    public function dropoffupdate(Request $request, $id)
    {
        $id = $request->id;

        $user_id = DropOff::where('id', $id)
            ->first()->user_id;

        $amount = DropOff::where('id', $id)
            ->first()->amount;

        $receiver_id = DropOff::where('id', $id)
            ->first()->receiver_id;

        $f_name = User::where('id', Auth::id())
            ->first()->first_name;

        $l_name = User::where('id', Auth::id())
            ->first()->last_name;

        $org_name = DropOff::where('id', $id)
            ->first()->collection_center;

        $status = DropOff::where('id', $id)
            ->first()->status;

        $weight = DropOff::where('id', $id)
            ->first()->weight;


        $user_type =  User::where('id', $user_id)->first()->user_type;

        if ($weight == null) {
            return back()->with('error', 'Agent has not approve drop off');
        }

        if ($amount == null) {
            return back()->with('error', 'Agent has not approve drop off');
        }

        if ($status == 1) {
            return back()->with('error', 'Drop off has been paid for');
        }

        $update_drop = DropOff::where('id', $id)
            ->update(['status' => 1]);

        $user_amount = User::where('id', $user_id)
            ->first()->wallet;

        $new_amount = $user_amount + $amount;

        $update = User::where('id', $user_id)
            ->update(['wallet' => $new_amount]);

        //create Credit transaction
        $transaction = new Transaction();
        $transaction->trans_id = Str::random(8);
        $transaction->reference = Str::random(15);
        $transaction->user_id = $user_id;
        $transaction->amount = $amount;
        $transaction->type = 'Credit';
        $transaction->user_type = $user_type;
        $transaction->org_name = $org_name;
        $transaction->staff_id = $l_name . " " . $f_name;
        $transaction->save();

        //Add to collection
        $collection = new Collection();
        $collection->item_id = 1;
        $collection->price_per_kg = 0;
        $collection->transport = 0;
        $collection->loader = 0;
        $collection->others = 0;
        $collection->item_weight = $weight;
        $collection->location_id = $receiver_id;
        $collection->amount = 0;
        $collection->center_type = 'drop_off';
        $collection->user_id = Auth::id();
        $collection->save();

        //get location id
        $get_location_id = Location::where('id', $receiver_id)
            ->first()->user_id;

        //add total weight
        $get_total = AgentRequest::where('user_id', $get_location_id)
            ->first()->total_weight;

        $new_total = $get_total + $weight;

        $update = AgentRequest::where('user_id', $get_location_id)
            ->update(['total_weight' => $new_total]);

        $get_user_email = User::where('id', $user_id)
            ->first();
        $receiveremail = $get_user_email->email;

        //send email
        $data = array(
            'fromsender' => 'info@kaltani.com', 'TRASH BASH',
            'subject' => "Wallet Updated",
            'bodyMessage' => "This email is to let you know your wallet has been credited with $amount",
            'toreceiver' => $receiveremail,
        );

        Mail::send('credit', $data, function ($message) use ($data) {
            $message->from($data['fromsender']);
            $message->to($data['toreceiver']);
            $message->subject($data['subject']);

        });

        //send app nofication to customer
        $user_firebaseToken = User::where('id', $user_id)
            ->first()->device_id;

        $SERVER_API_KEY = env('FCM_SERVER_KEY');

        $data = [
            "registration_ids" => array($user_firebaseToken),
            "notification" => [
                "title" => 'Wallet Credit',
                "body" => "Your Wallet has been credited  with $amount ",
            ],

            "data" => [
                "message" => "Wallet Credited",
            ],

        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        return back()->with('message', 'Drop off updated successfully');

    }

    public function dropoffreject(Request $request)
    {
        $id = $request->id;
        $reason = $request->reason;

        $user_id = DropOff::where('id', $id)
            ->first()->user_id;

        $amount = DropOff::where('id', $id)
            ->first()->amount;

        $receiver_id = DropOff::where('id', $id)
            ->first()->receiver_id;

        $sender_id = DropOff::where('id', $id)
            ->first()->sender_id;

        $f_name = User::where('id', Auth::id())
            ->first()->first_name;

        $l_name = User::where('id', Auth::id())
            ->first()->last_name;

        $org_name = DropOff::where('id', $id)
            ->first()->collection_center;

        $status = DropOff::where('id', $id)
            ->first()->status;

        $weight = DropOff::where('id', $id)
            ->first()->weight;

        if ($weight == null) {
            return back()->with('error', 'Agent has not approve drop off');
        }

        if ($amount == null) {
            return back()->with('error', 'Agent has not approve drop off');
        }

        if ($status == 1) {
            return back()->with('error', 'Drop off has been paid for');
        }

        $update_drop = DropOff::where('id', $id)
            ->update(['status' => 4,
                'reason' => $reason]);

        $get_user_email = User::where('id', $user_id)
            ->first();
        $senderemail = $get_user_email->email;

        //send email to customer

        $details = [
            'subject' => 'Cardy Daily Rate',
            'fname' => $f_name,
            'reason' => $reason,
          ];

          Mail::to($senderemail)->send(new Reject($details));



        // $data = array(
        //     'fromsender' => 'info@kaltani.com', 'TRASH BASH',
        //     'subject' => "Drop Off Rejected",
        //     'toreceiver' => $senderemail,
        //     'fname' => $f_name,
        //     'reason' => $reason,

        // );

        // Mail::send('reject', ["data1" => $data], function ($message) use ($data) {
        //     $message->from($data['fromsender']);
        //     $message->to($data['toreceiver']);
        //     $message->subject($data['subject']);
        // });



        $center_email = User::where('location_id',$receiver_id)
        ->first()->email;

        $center_f_name = User::where('location_id',$receiver_id)
        ->first()->first_name;

        //send email to agent

        $details = [
            'subject' => 'Cardy Daily Rate',
            'fname' => $center_f_name,
            'reason' => $reason,
          ];

          Mail::to($center_email)->send(new Reject($details));

        // $data = array(
        //     'fromsender' => 'info@kaltani.com', 'TRASH BASH',
        //     'subject' => "Drop Off Rejected",
        //     'toreceiver' => $center_email,
        //     'reason' => $reason,

        // );

        // Mail::send('reject', ["data1" => $data], function ($message) use ($data) {
        //     $message->from($data['fromsender']);
        //     $message->to($data['toreceiver']);
        //     $message->subject($data['subject']);
        // });



        //send app nofication to customer
        $user_firebaseToken = User::where('id', $user_id)
            ->first()->device_id;

        $SERVER_API_KEY = env('FCM_SERVER_KEY');

        $data = [
            "registration_ids" => array($user_firebaseToken),
            "notification" => [
                "title" => 'Drop Off Rejected',
                "body" => "Your drop off has been rejected",
            ],

            "data" => [
                "message" => "Drop off Rejected",
            ],

        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        return back()->with('message', 'Drop off rejected successfully');

    }

    public function terms()
    {

        return view('terms');
    }

    public function settings()
    {

        $customer_price_per_kg = Rate::where('id', 1)
            ->first()->rate;

            $agent_price_per_kg = Rate::where('id', 4)
            ->first()->rate;

        $transfer_fee = Rate::where('id', 2)
            ->first()->rate;



        return view('setting', compact('customer_price_per_kg', 'agent_price_per_kg', 'transfer_fee'));

    }

    public function agent_charge_per_kg(Request $request)
    {

        $price_per_kg = $request->price_per_kg;

        $update = Rate::where('id', 4)
            ->update(['rate' => $price_per_kg]);

        return back()->with('message', 'Price per KG updated successfully');

    }

    public function customer_charge_per_kg(Request $request)
    {

        $price_per_kg = $request->price_per_kg;

        $update = Rate::where('id', 1)
            ->update(['rate' => $price_per_kg]);

        return back()->with('message', 'Price per KG updated successfully');

    }

    public function transfer_fee(Request $request)
    {

        $transfer_fee = $request->transfer_fee;

        $update = Rate::where('id', 2)
            ->update(['rate' => $transfer_fee]);

        return back()->with('message', 'Transfer Fee updated successfully');

    }

    public function viewdropoff($id)
    {

        $drop_off = DropOff::find($id);
        $drop_off_details = DropOff::where('id', $drop_off->id)->get();

        $get_weight = DropOff::where('id', $drop_off->id)
            ->first();
        $weight = $get_weight->weight;

        $get_amount = DropOff::where('id', $drop_off->id)
            ->first();
        $amount = $get_amount->amount;

        $get_collection_center = DropOff::where('id', $drop_off->id)
            ->first();
        $collection_center = $get_collection_center->collection_center;

        $get_customer_name = DropOff::where('id', $drop_off->id)
            ->first();
        $customer = $get_customer_name->customer;

        $get_customer_image = DropOff::where('id', $drop_off->id)
            ->first();
        $image = $get_customer_image->image;

        $get_agent_image = DropOff::where('id', $drop_off->id)
            ->first();
        $agent_image = $get_agent_image->agent_image;

        $get_status = DropOff::where('id', $drop_off->id)
            ->first();
        $status = $get_status->status;

        $get_order_id = DropOff::where('id', $drop_off->id)
            ->first();
        $order_id = $get_order_id->order_id;

        $get_order_id = DropOff::where('id', $drop_off->id)
            ->first();
        $id = $get_order_id->id;

        $get_order_id = DropOff::where('id', $drop_off->id)
            ->first();
        $reason = $get_order_id->reason;



        $drop_off_list = DropOff::all();

        return view('drop_off_details', compact('drop_off_list', 'id', 'order_id', 'status', 'agent_image', 'image', 'customer', 'collection_center', 'reason', 'amount', 'drop_off', 'weight'));
    }

    public function dropoffDelete($id)
    {

        $drop = DropOff::find($id);
        $drop->delete();

        return redirect('/drop-off')->with('message', 'Drop Off Deleted Successfully');
    }

    public function agent_request()
    {
        $pending_request = AgentRequest::where('status', '0')->count();
        $approved_agent = AgentRequest::where('status', 1)->count();

        $agent_list = AgentRequest::orderBy('created_at', 'DESC')->paginate(10);

        $user = User::all();

        return view('agent-request', compact('agent_list', 'pending_request', 'approved_agent', 'user'));

    }

    public function agent_request_update(Request $request)
    {

        $id = $request->id;

        $user_id = AgentRequest::where('id', $id)
            ->first()->user_id;

        $status = AgentRequest::where('id', $id)
            ->first()->status;

        if ($status == 1) {

            return back()->with('error', 'Agent has already been verfied');
        }

        $address = AgentRequest::where('id', $id)
            ->first()->address;

        $lga = AgentRequest::where('id', $id)
            ->first()->lga;

        $city = AgentRequest::where('id', $id)
            ->first()->city;

        $state = AgentRequest::where('id', $id)
            ->first()->state;

        $longitude = AgentRequest::where('id', $id)
            ->first()->longitude;

        $latitude = AgentRequest::where('id', $id)
            ->first()->latitude;

        $agent = "agent";

        $org_name = "TRASH BASH AGENT" . " " . random_int(100, 999);

        $location = new Location();
        $location->name = $org_name;
        $location->address = $address;
        $location->lga = $lga;
        $location->city = $city;
        $location->state = $state;
        $location->longitude = $longitude;
        $location->latitude = $latitude;
        $location->user_id = $user_id;
        $location->type = 'd';
        $location->save();

        $items = AgentRequest::find($id);
        $items->status = 1;
        $items->org_name = $org_name;
        $items->save();

        $get_agent_location_id = Location::where('user_id', $user_id)
            ->first()->id;

        $update = User::where('id', $user_id)
            ->update([

                'user_type' => "agent",
                'role_id' => 3,
                'location_id' => $get_agent_location_id,

            ]);

        $user_firebaseToken = User::where('id', $user_id)
            ->first()->device_id;

        $SERVER_API_KEY = env('FCM_SERVER_KEY');

        $data = [
            "registration_ids" => array($user_firebaseToken),
            "notification" => [
                "title" => 'Request Approved',
                "body" => "Your agent request has been successfuly approved.",
            ],

            "data" => [
                "message" => "Request Approved",
            ],

        ];

        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        $user_email = User::where('id', $user_id)
            ->first()->email;

        //send email to sender
        $data = array(
            'fromsender' => 'info@kaltani.com', 'TRASH BASH',
            'subject' => "Agent Approval",
            'toreceiver' => $user_email,
        );

        Mail::send('agent_approval', $data, function ($message) use ($data) {
            $message->from($data['fromsender']);
            $message->to($data['toreceiver']);
            $message->subject($data['subject']);

        });

        return redirect('/agent-request')->with('message', 'Agent Updated Successfully');
    }

    //Transaction
    public function transactions()
    {

        $key = env('EKEY');

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://web.sprintpay.online/api/get-account',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer $key"
        ),
        ));


        $var = curl_exec($curl);
        curl_close($curl);

        $var = json_decode($var);

        $main_wallet = $var->data->main_wallet;


        $money_out_to_customer = Transaction::where('type', 'debit')->sum('amount');

        $transactions = Transaction::all();

        $users = User::all();

        return view('transactions', compact('transactions','main_wallet', 'money_out_to_customer', 'users'));

    }

    public function fund_agent()
    {
        $get_all_agent = User::where('user_type', 'agent')->first();

        $rate = Rate::where('id', 4)
        ->first()->rate;

        $agents = AgentRequest::where('status', '1')->get();

        $fund_transaction = Transaction::where('type', 'Agent Credit')
            ->get();

        $agentfunds = Transaction::where('type', 'Agent Credit')
            ->sum('amount');

        return view('fund-agent', compact('agents', 'fund_transaction', 'rate', 'agentfunds'));

    }

    public function fund_agent_now(Request $request)
    {
        $user_id = $request->user_id;
        $amount = $request->amount;
        $weight = $request->weight;





        $user_type = User::where('id', $user_id)->first()->user_type;

        $initial_weight = AgentRequest::where('user_id', $request->user_id)
            ->first()->total_weight;


        if($initial_weight < $weight){
            return back()->with('error', "Insufficient Agent Weight");
        }

        $new_weight = $initial_weight - $weight;

        $update = AgentRequest::where('user_id', $user_id)
            ->update(['total_weight' => $new_weight]);

        $org_name = AgentRequest::where('user_id', $request->user_id)
            ->first()->org_name;

        $get_amount = User::where('id', $user_id)
            ->first()->wallet;

        $staff_first = User::where('id', Auth::id())
            ->first()->first_name;
        $staff_last = User::where('id', Auth::id())
            ->first()->last_name;

        //add money to agent

        $credit = (int) $amount + (int) $get_amount;

        $update = User::where('id', $user_id)
            ->update(['wallet' => $credit]);

        $transaction = new Transaction();
        $transaction->user_id = $user_id;
        $transaction->user_type = $user_type;
        $transaction->staff_id = $staff_first . " " . $staff_last;
        $transaction->reference = Str::random(10);
        $transaction->amount = $amount;
        $transaction->type = 'Agent Credit';
        $transaction->org_name = $org_name;
        $transaction->weight = $weight;
        $transaction->trans_id = Str::random(10);
        $transaction->save();

        //send agent nofication
        $user_firebaseToken = User::where('id', $user_id)
            ->first()->device_id;

        $SERVER_API_KEY = env('FCM_SERVER_KEY');

        $data = [
            "registration_ids" => array($user_firebaseToken),
            "notification" => [
                "title" => 'Wallet Credited',
                "body" => "Your Wallet has been credited  with NGN $amount ",
            ],

            "data" => [
                "message" => "Wallet Credited",
            ],

        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        return back()->with('message', "Agent has been funded  NGN $amount");

    }

    public function itemList()
    {
        $items = Item::all();
        return view('item', compact('items'));
    }

    public function itemEdit($id)
    {
        $item = Item::find($id);
        return view('item_edit', compact('item'));
    }
    public function itemEditUpdate(Request $request, $id)
    {
        $items = Item::find($id);
        $items->item = $request->input('item');
        $items->save();

        return redirect('/item')->with('message', 'Item Updated Successfully');
    }
    public function itemDelete($id)
    {
        $items = Item::find($id);
        $items->delete();
        return redirect('/item')->with('message', 'Item Deleted Successfully');
    }

    public function sortedDelete($id)
    {
        $item = Sorting::find($id);
        //dd($item);
        $item->delete();
        return back()->with('message', 'Sorting Deleted Successfully');
    }

    public function bailedEdit($id)
    {
        $item = BailingItem::find($id);
        return view('bailing_item_edit', compact('item'));
    }
    public function bailItemEditUpdate(Request $request, $id)
    {
        $items = BailingItem::find($id);
        $items->item = $request->input('bailing_item');
        $items->save();

        return redirect('/bailing_item')->with('message', 'Bailed Item Updated Successfully');
    }
    public function bailedDelete($id)
    {
        $items = BailingItem::find($id);
        $items->delete();
        return redirect('/bailing_item')->with('message', 'Bailed Item Deleted Successfully');
    }

    public function sorted(Request $request)
    {
        try {
            //dd($request->all());
            $result = ($request->Clean_Clear + $request->Others + $request->Green_Colour + $request->Trash);
            //dd($result);
            $t = CollectedDetails::where('location_id', $request->input('location_id'))->first();
            if (empty($t)) {
                return back()->with('error', 'No Record Found');
            } else {

                if ($result > $t->collected) {

                    return back()->with('error', 'Insufficent Collected ');
                }
            }
            $sort = new Sorting();
            $sort->item_id = $request->item_id;
            $sort->Clean_Clear = $request->Clean_Clear ?? 0;
            $sort->Green_Colour = $request->Green_Colour ?? 0;
            $sort->Others = $request->Others ?? 0;
            $sort->Trash = $request->Trash ?? 0;
            $sort->Caps = $request->Caps ?? 0;
            $sort->location_id = $request->location_id;
            $sort->created_at = $request->created_at;
            $sort->user_id = Auth::id();
            //dd($sort);
            $sort->save();

            $sorted = ($sort->Clean_Clear + $sort->Others + $sort->Green_Colour + $sort->Trash);
            //dd($sorted);
            $t = CollectedDetails::where('location_id', $request->location_id)->decrement('collected', $sorted);

            // if (!empty($t) ) {
            //     $t->decrement('collected', $sorted);
            // }

            $dataset = [
                'Clean_Clear' => $request->Clean_Clear ?? 0,
                'Green_Colour' => $request->Green_Colour ?? 0,
                'Others' => $request->Others ?? 0,
                'Trash' => $request->Trash ?? 0,
                'Caps' => $request->Caps ?? 0,
            ];
            //dd($tweight);

            $other_value_history = [

                'location_id' => $request->location_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            $other_value = [
                'user_id' => Auth::id(),
                'location_id' => $request->location_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            $old_sorting = DB::table('sort_details')->where('location_id', $request->location_id)->first();

            if (empty($old_sorting)) {

                DB::table('sort_details')->insert([
                    array_merge($dataset, $other_value),
                ]);
            } else {

                //dd($new_dataset);
                $updated = SortDetails::where('location_id', $request->location_id)->first();
                $updated->update(['Clean_Clear' => ($updated->Clean_Clear + $request->Clean_Clear ?? 0)]);
                $updated->update(['Green_Colour' => ($updated->Green_Colour + $request->Green_Colour ?? 0)]);
                $updated->update(['Others' => ($updated->Others + $request->Others ?? 0)]);
                $updated->update(['Trash' => ($updated->Trash + $request->Trash ?? 0)]);
                $updated->update(['Caps' => ($updated->Caps + $request->Caps ?? 0)]);

            }

            return back()->with('message', 'Sorting Created Successfully');
        } catch (Exception $e) {
            return back()->with('error', 'Error');
        }
    }

    public function bailed(Request $request)
    {
        try {

            $result = ($request->Clean_Clear + $request->Others + $request->Green_Colour + $request->Trash);

            $t = SortDetails::where('location_id', $request->location_id)->first();
            if (empty($t)) {
                return back()->with('error', 'No Collection Found');
            }
            $tsorted = ($t->Clean_Clear + $t->Others + $t->Green_Colour + $t->Trash);
            if ($result > $tsorted) {
                return back()->with('error', 'Insufficent Sorted');
            }
            $checkSort = SortDetails::where('location_id', $request->location_id)->first();
            if (empty($checkSort)) {
                return back()->with('error', 'No Collection Found');
            }
            if ($request->Clean_Clear > $checkSort->Clean_Clear) {

                return back()->with('error', 'Insufficent Clean Clear');

            } elseif ($request->Green_Colour > $checkSort->Green_Colour) {
                return back()->with('error', 'Insufficent Green Colour');
            } elseif ($request->Others > $checkSort->Others) {
                return back()->with('error', 'Insufficent Others');
            } elseif ($request->Trash > $checkSort->Trash) {
                return back()->with('error', 'Insufficent Trash');
            }

            $bailing = new Bailing();
            $bailing->item_id = $request->item_id;
            $bailing->Clean_Clear = $request->Clean_Clear ?? 0;
            $bailing->Green_Colour = $request->Green_Colour ?? 0;
            $bailing->Others = $request->Others ?? 0;
            $bailing->Trash = $request->Trash ?? 0;
            $bailing->location_id = $request->location_id;
            $bailing->user_id = Auth::id();
            //dd($bailing);
            $bailing->save();

            $bailed = ($bailing->Clean_Clear + $bailing->Others + $bailing->Green_Colour + $bailing->Trash);

            $dataset = [
                'Clean_Clear' => $request->Clean_Clear ?? 0,
                'Green_Colour' => $request->Green_Colour ?? 0,
                'Others' => $request->Others ?? 0,
                'Trash' => $request->Trash ?? 0,
            ];
            //dd($tweight);

            $other_value_history = [
                'location_id' => $request->location_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            $other_value = [
                'user_id' => Auth::id(),
                'location_id' => $request->location_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            $old_bailing = DB::table('bailed_details')->where('location_id', $request->location_id)->first();
            //dd(empty($old_sorting));
            if (empty($old_bailing)) {

                DB::table('bailed_details')->insert([
                    array_merge($dataset, $other_value),
                ]);
            } else {

                $updated = BailedDetails::where('location_id', $request->location_id)->first();
                //dd($updated->Clean_Clear);
                $updated->increment('Clean_Clear', ($request->Clean_Clear ?? 0));
                $updated->increment('Green_Colour', ($request->Green_Colour ?? 0));
                $updated->increment('Others', ($request->Others ?? 0));
                $updated->increment('Trash', ($request->Trash ?? 0));
            }

            $updated = SortDetails::where('location_id', $request->location_id)->first();
            //dd($updated->Clean_Clear);
            $updated->decrement('Clean_Clear', ($request->Clean_Clear ?? 0));
            $updated->decrement('Green_Colour', ($request->Green_Colour ?? 0));
            $updated->decrement('Others', ($request->Others ?? 0));
            $updated->decrement('Trash', ($request->Trash ?? 0));

            return back()->with('Message', 'Bailing Successfully');

        } catch (Exception $e) {
            return back()->with('error', $e);
        }

    }
    public function transferd(Request $request)
    {
        try {
            $result = ($request->Clean_Clear + $request->Others + $request->Green_Colour + $request->Trash);
            $t = BailedDetails::where('location_id', $request->collection_id)->first();
            if (empty($t)) {
                return back()->with('error', 'No Record Found');

            }
            $tbailed = ($t->Clean_Clear + $t->Others + $t->Green_Colour + $t->Trash);
            if ($result > $tbailed) {
                return back()->with('error', 'Insufficent Bailed ');
            }
            $checkSort = BailedDetails::where('location_id', $request->collection_id)->first();
            if (empty($checkSort)) {
                return back()->with('error', 'No Collection Found');
            }
            if ($request->Clean_Clear > $checkSort->Clean_Clear) {

                return back()->with('error', 'Insufficent Clean Clear');

            } elseif ($request->Green_Colour > $checkSort->Green_Colour) {
                return back()->with('error', 'Insufficent Green Colour');
            } elseif ($request->Others > $checkSort->Others) {
                return back()->with('error', 'Insufficent Others');
            } elseif ($request->Trash > $checkSort->Trash) {
                return back()->with('error', 'Insufficent Trash');
            }

            $transfer = new Transfer();
            $transfer->Clean_Clear = $request->Clean_Clear ?? 0;
            $transfer->Green_Colour = $request->Green_Colour ?? 0;
            $transfer->Others = $request->Others ?? 0;
            $transfer->Trash = $request->Trash ?? 0;
            $transfer->location_id = $request->collection_id;
            $transfer->factory_id = $request->factory_id;
            $transfer->collection_id = $request->collection_id;
            $transfer->user_id = Auth::id();
            $transfer->status = 0;
            //dd($transfer);
            $transfer->save();

            $transfered = ($transfer->Clean_Clear + $transfer->Others + $transfer->Green_Colour + $transfer->Trash);

            $dataset = [
                'Clean_Clear' => $request->Clean_Clear ?? 0,
                'Green_Colour' => $request->Green_Colour ?? 0,
                'Others' => $request->Others ?? 0,
                'Trash' => $request->Trash ?? 0,
            ];
            //dd($tweight);

            $other_value_history = [
                'location_id' => $request->collection_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            $other_value = [
                'user_id' => Auth::id(),
                'location_id' => $request->collection_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            $old_transfer = DB::table('transfer_details')->where('location_id', $request->collection_id)->first();

            if (empty($old_transfer)) {

                DB::table('transfer_details')->insert([
                    array_merge($dataset, $other_value),
                ]);
            } else {

                //dd($new_dataset);
                $updated = TransferDetails::where('location_id', $request->collection_id)->first();
                $updated->increment('Clean_Clear', ($request->Clean_Clear ?? 0));
                $updated->increment('Green_Colour', ($request->Green_Colour ?? 0));
                $updated->increment('Others', ($request->Others ?? 0));
                $updated->increment('Trash', ($request->Trash ?? 0));
            }

            $sortcheck = BailedDetails::where('location_id', $request->factory_id)->first();
            if (empty($sortcheck)) {
                $sortdetails = new BailedDetails();
                $sortdetails->Clean_Clear = $request->Clean_Clear ?? 0;
                $sortdetails->Green_Colour = $request->Green_Colour ?? 0;
                $sortdetails->Others = $request->Others ?? 0;
                $sortdetails->Trash = $request->Trash ?? 0;
                $sortdetails->Caps = $request->Caps ?? 0;
                $sortdetails->location_id = $request->factory_id;
                $sortdetails->user_id = Auth::id();
                $sortdetails->save();

            } else {

                $updated = BailedDetails::where('location_id', $request->factory_id)->first();
                $updated->update(['Clean_Clear' => ($updated->Clean_Clear + ($request->Clean_Clear ?? 0))]);
                $updated->update(['Green_Colour' => ($updated->Green_Colour + ($request->Green_Colour ?? 0))]);
                $updated->update(['Others' => ($updated->Others + ($request->Others ?? 0))]);
                $updated->update(['Trash' => ($updated->Trash + ($request->Trash ?? 0))]);
                $updated->update(['Caps' => ($updated->Caps + ($request->Caps ?? 0))]);

            }

            $updated = BailedDetails::where('location_id', $request->collection_id)->first();
            //dd($updated->Clean_Clear);
            $updated->decrement('Clean_Clear', ($request->Clean_Clear ?? 0));
            $updated->decrement('Green_Colour', ($request->Green_Colour ?? 0));
            $updated->decrement('Others', ($request->Others ?? 0));
            $updated->decrement('Trash', ($request->Trash ?? 0));

            $notification_id = User::where('factory_id', $request->factory_id)
                ->whereNotNull('device_id')
                ->pluck('device_id');
            //dd($notification_id);
            if (!empty($notification_id)) {

                $factory = Location::where('id', $request->factory_id)->first();
                $response = Http::withHeaders([
                    'Authorization' => 'key=AAAAva2Kaz0:APA91bHSiOJFPwd-9-2quGhhiyCU263oFWWrnYKtmuF1jGmDSMBHWiFkGy3tiaP3bLhJNMy9ki0YY061y5riGULckZtBkN9WkDZGX5X9HN60a2NvwHFR8Yevnat_zHzomC5O7AkdYwT8',
                    'Content-Type' => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', [
                    "registration_ids" => $notification_id,
                    "notification" => [
                        "title" => "Transfer notification",
                        "body" => "Incomming Transfer from " . $factory->name,
                    ],
                ]);
                $notification = $response->json('results');
            }

            return back()->with('message', 'Transfer Successfully');
        } catch (Exception $e) {
            return back()->with('error', $e);
        }

        //dd($transfer);

    }
    public function sorting()
    {

        $item = Item::all();
        $bailingItems = BailingItem::all();
        //dd($bailingItems);
        $collection = Location::all();

        $sorting = Sorting::orderBy('created_at', 'desc')->get();

        return view('sorting', compact('bailingItems', 'item', 'collection', 'sorting'));
    }
    public function bailing()
    {

        $item = Item::all();
        $bailingItems = BailingItem::all();
        //dd($bailingItems);
        $collection = Location::all();
        $sorting = Bailing::all();
        //dd($sorting);

        return view('bailing', compact('bailingItems', 'item', 'collection', 'sorting'));
    }
    public function transfering()
    {

        $item = Item::all();
        $bailingItems = BailingItem::all();
        //dd($bailingItems);
        $collection = Location::all();
        $factory = Location::where('type', 'f')->get();
        $transfer = Transfer::all();
        //dd($sorting);

        return view('transfer', compact('bailingItems', 'item', 'collection', 'transfer', 'factory'));
    }
    public function viewsorting($id)
    {

        $st = Sorting::where('id', $id)->first();
        //dd($st);
        $sorting = Sorting::where('location_id', $st->location_id)
            ->get();
        $bailing = Bailing::where('location_id', $st->location_id)
            ->get();
        //$totals = Total::where('location_id',$st->location_id)->first();
        $sorted = SortDetails::where('location_id', $st->location_id)->sum(\DB::raw('Clean_Clear + Green_Colour + Others + Trash + Caps'));
        $cl = CollectedDetails::where('location_id', $st->location_id)->first();
        $collected = $cl->collected ?? 0;
        $bailed = BailedDetails::where('location_id', $st->location_id)->sum(\DB::raw('Clean_Clear + Green_Colour + Others + Trash'));
        return view('viewSortingDetails', compact('sorting', 'bailing', 'sorted', 'bailed', 'collected'));
    }

    public function viewtransfer($id)
    {

        $st = Transfer::where('id', $id)->first();
        //dd($st);
        $sorting = Sorting::where('location_id', $st->location_id)
            ->get();
        $bailing = Bailing::where('location_id', $st->location_id)
            ->get();
        $transfer = Transfer::where('location_id', $st->location_id)
            ->get();
        $weightIn = Recycle::sum('item_weight_input');
        $weightOut = Recycle::sum('item_weight_output');
        $totals = TransferDetails::where('location_id', $st->location_id)->first();
        $sorted = SortDetails::where('location_id', $st->location_id)->sum(\DB::raw('Clean_Clear + Green_Colour + Others + Trash + Caps'));
        $bailed = BailedDetails::where('location_id', $st->location_id)->sum(\DB::raw('Clean_Clear + Green_Colour + Others + Trash'));
        return view('viewTransfer', compact('sorting', 'bailing', 'totals', 'transfer', 'weightIn', 'weightOut', 'sorted', 'bailed'));
    }

    public function viewcollection($id)
    {

        $collection = Collection::find($id);
        $collect = Collection::where('location_id', $collection->location_id)->get();
        //dd($collect);
        $sorted = SortDetails::where('location_id', $collection->location_id)->sum(\DB::raw('Clean_Clear + Green_Colour + Others + Trash + Caps'));
        $cl = CollectedDetails::where('location_id', $collection->location_id)->first();
        $collected = $cl->collected ?? 0;
        $bailed = BailedDetails::where('location_id', $collection->location_id)->sum(\DB::raw('Clean_Clear + Green_Colour + Others + Trash'));
        return view('collectionDetails', compact('collect', 'collected', 'sorted', 'bailed'));
    }

    public function viewcollectioncenter($id)
    {

        $collection = Location::find($id);
        $collect = Collection::where('location_id', $collection->id)->get();
        $sorted = SortDetails::where('location_id', $collection->id)->sum(\DB::raw('Clean_Clear + Green_Colour + Others + Trash + Caps'));
        $cl = CollectedDetails::where('location_id', $collection->id)->first();
        //dd($cl);
        $collected = $cl->collected ?? 0;
        $total_weight = Collection::where('location_id', $collection->id)->sum('item_weight');

        $bailed = BailedDetails::where('location_id', $collection->id)->sum(\DB::raw('Clean_Clear + Green_Colour + Others + Trash'));

        return view('collection_center_details', compact('collect', 'collected', 'sorted', 'bailed', 'total_weight'));
    }

    public function createFactory(Request $request)
    {
        $location = new Factory();
        $location->name = $request->input('name');
        $location->address = $request->input('address');
        $location->city = $request->input('city');
        $location->state = $request->input('state');
        $location->user_id = Auth::id();
        $location->save();

        return back()->with('message', 'Factory Created Successfully');
    }

    public function location(Request $request)
    {
        $location = new Location();
        $location->name = $request->input('name');
        $location->address = $request->input('address');
        $location->city = $request->input('city');
        $location->state = $request->input('state');
        $location->type = $request->input('type');
        $location->user_id = Auth::id();
        $location->save();

        return back()->with('message', 'Location Created Successfully');
    }
    public function report()
    {
        $report = History::all();
        return view('report', compact('report'));
    }

    public function recycle(Request $request)
    {

        $trans = Transfer::where('factory_id', $request->factory_id)->first();
        if (empty($trans)) {
            return back()->with('error', 'No Transfer Found');
        }
        $total = BailedDetails::where('location_id', $request->factory_id)->first();
        if (empty($total)) {
            return back()->with('error', 'No Transfer  Found');
        }

        $tall = ($total->Clean_Clear + $total->Others + $total->Green_Colour + $total->Trash);
        if ($request->item_weight_input > $tall) {
            return back()->with('error', 'Insufficent Transfer');
        }
        $checkrecycle = RecyclesDetails::where('location_id', $request->factory_id)->first();
        if ($request->Clean_Clear > $total->Clean_Clear) {

            return back()->with('error', 'Insufficent Clean Clear');

        } elseif ($request->Green_Colour > $total->Green_Colour) {
            return back()->with('error', 'Insufficent Green Colour');
        } elseif ($request->Others > $total->Others) {
            return back()->with('error', 'Insufficent Others');
        } elseif ($request->Trash > $total->Trash) {
            return back()->with('error', 'Insufficent Trash');
        }
        if (empty($checkrecycle)) {
            $ry = new RecyclesDetails();
            $ry->Clean_Clear = $request->Clean_Clear ?? 0;
            $ry->Green_Colour = $request->Green_Colour ?? 0;
            $ry->Others = $request->Others ?? 0;
            $ry->Trash = $request->Trash ?? 0;
            $ry->location_id = $request->factory_id;
            $ry->save();
        } else {
            $updated = RecyclesDetails::where('location_id', $request->factory_id)->first();
            $updated->update(['Clean_Clear' => ($updated->Clean_Clear + $request->Clean_Clear ?? 0)]);
            $updated->update(['Green_Colour' => ($updated->Green_Colour + $request->Green_Colour ?? 0)]);
            $updated->update(['Others' => ($updated->Others + $request->Others ?? 0)]);
            $updated->update(['Trash' => ($updated->Trash + $request->Trash ?? 0)]);

        }
        $updated = BailedDetails::where('location_id', $request->factory_id)->first();
        $updated->update(['Clean_Clear' => ($updated->Clean_Clear - $request->Clean_Clear ?? 0)]);
        $updated->update(['Green_Colour' => ($updated->Green_Colour - $request->Green_Colour ?? 0)]);
        $updated->update(['Others' => ($updated->Others - $request->Others ?? 0)]);
        $updated->update(['Trash' => ($updated->Trash - $request->Trash ?? 0)]);

        $weightIn = ($request->Clean_Clear + $request->Green_Colour + $request->Others + $request->Trash);
        $recycle = Recycle::create([
            "item_weight_input" => $weightIn ?? 0,
            "costic_soda" => $request->costic_soda ?? 0,
            "detergent" => $request->detergent ?? 0,
            "item_weight_output" => $request->item_weight_output ?? 0,
            "Clean_Clear" => $request->Clean_Clear ?? 0,
            "Green_Colour" => $request->Green_Colour ?? 0,
            "Others" => $request->Others ?? 0,
            "Trash" => $request->Trash ?? 0,
            "factory_id" => $request->factory_id,
            "user_id" => Auth::id(),

        ]);

        $recycled = $request->item_weight_output;

        $factory_id = $request->factory_id;

        if (FactoryTotal::where('factory_id', $factory_id)->exists()) {
            # code...
            $t = FactoryTotal::where('factory_id', $factory_id)->first();
            $t->update(['recycled' => ($t->recycled + $recycled)]);
            $t->update(['flesk' => ($t->flesk + $request->item_weight_output)]);
        } else {
            $total = new FactoryTotal();
            $total->recycled = $recycled ?? 0;
            $total->flesk = $request->item_weight_output ?? 0;
            $total->factory_id = $request->factory_id;
            //dd($total);
            $total->save();
        }

        return back()->with('message', 'Recycle Created Successfully');

    }
    public function recycled(Request $request)
    {
        $recycled = Recycle::all();
        $factory = Location::where('type', 'f')->get();
        return view('recycle', compact('recycled', 'factory'));
    }

    public function sales(Request $request)
    {
        try {
            //dd($request->all());
            $sales = new Sales();
            $sales->item_weight = $request->item_weight ?? 0;
            $sales->customer_name = $request->customer_name;
            $sales->price_per_ton = $request->price_per_ton ?? 0;
            $sales->freight = $request->freight ?? 0;
            $sales->currency = $request->currency;
            if ($request->currency == "NGN") {
                $sales->amount_ngn = $request->amount ?? 0;
            }
            if ($request->currency == "USD") {
                $sales->amount_usd = $request->amount ?? 0;
            }
            $sales->location_id = $request->factory_id;
            $sales->customer_name = $request->customer_name;
            $sales->user_id = Auth::id();
            $sales->save();

            $sales = $request->amount ?? 0;
            $weight = $request->item_weight ?? 0;
            $recycled = $request->item_weight_output ?? 0;

            $factory_id = $request->factory_id;

            if (FactoryTotal::where('factory_id', $factory_id)->exists()) {
                # code...
                $t = FactoryTotal::where('factory_id', $factory_id)->first();
                $t->increment('sales', $sales);
                $t->decrement('recycled', $weight);
                $t->decrement('flesk', $weight);
            } else {
                $total = new FactoryTotal();
                $total->sales = $sales;
                $total->factory_id = $request->factory_id;
                $total->save();
            }
            return back()->with('message', 'Sales Created Successfully');

        } catch (Exception $e) {
            return response()->json([
                'status' => $this->FailedStatus,
                'message' => 'Error',
                'errors' => $e->getMessage(),
            ], 401);
        }

    }

    public function salesp()
    {
        $recycled = Recycle::all();
        $sales = Sales::all();
        $salesdetailsusd = SalesDetails::all()->sum('amount_usd');
        $salesdetailsngn = SalesDetails::all()->sum('amount_ngn');
        $cl = SalesDetails::all()->sum('Clean_Clear');
        $gc = SalesDetails::all()->sum('Green_Colour');
        $oth = SalesDetails::all()->sum('Others');
        $trh = SalesDetails::all()->sum('Trash');

        $susd = Sales::all()->sum('amount_usd');
        $sngn = Sales::all()->sum('amount_ngn');
        $weight = Sales::all()->sum('item_weight');
        $salesfreight = Sales::all()->sum('freight');

        $salesweight = $cl + $gc + $oth + $trh + $weight;
        $salesusd = $salesdetailsusd + $susd;
        $salesngn = $salesdetailsngn + $sngn;
        //dd($salesweight);
        $salesdetailsfreight = SalesDetails::all()->sum('freight');
        $sfreight = Sales::all()->sum('freight');
        $salesfreight = $salesdetailsfreight + $sfreight;
        $factory = Location::where('type', 'f')->get();
        return view('sales', compact('recycled', 'sales', 'factory', 'salesusd', 'salesngn', 'salesweight', 'salesfreight'));
    }
    public function salesB()
    {
        $recycled = Recycle::all();
        $sales = SalesDetails::all();
        $factory = Location::where('type', 'c')->get();
        $salesdetailsusd = SalesDetails::all()->sum('amount_usd');
        $salesdetailsngn = SalesDetails::all()->sum('amount_ngn');
        $cl = SalesDetails::all()->sum('Clean_Clear');
        $gc = SalesDetails::all()->sum('Green_Colour');
        $oth = SalesDetails::all()->sum('Others');
        $trh = SalesDetails::all()->sum('Trash');

        $susd = Sales::all()->sum('amount_usd');
        $sngn = Sales::all()->sum('amount_ngn');
        $weight = Sales::all()->sum('item_weight');
        $salesfreight = Sales::all()->sum('freight');

        $salesweight = $cl + $gc + $oth + $trh + $weight;
        $salesusd = $salesdetailsusd + $susd;
        $salesngn = $salesdetailsngn + $sngn;
        //dd($salesweight);
        $salesdetailsfreight = SalesDetails::all()->sum('freight');
        $sfreight = Sales::all()->sum('freight');
        $salesfreight = $salesdetailsfreight + $sfreight;
        return view('salesBailed', compact('recycled', 'sales', 'factory', 'salesusd', 'salesngn', 'salesweight', 'salesfreight'));
    }

    public function saleBailed(Request $request)
    {
        try {
            $result = ($request->Clean_Clear + $request->Others + $request->Green_Colour + $request->Trash);
            $t = Total::where('location_id', $request->collection_id)->first();
            if (empty($t)) {
                return back()->with('error', 'No Record Found');

            }

            if ($result > $t->bailed) {
                return back()->with('error', 'Insufficent Bailed ');
            }
            $checkSort = BailedDetails::where('location_id', $request->collection_id)->first();
            if (empty($checkSort)) {
                return back()->with('error', 'No Collection Found');
            }
            if ($request->Clean_Clear > $checkSort->Clean_Clear) {

                return back()->with('error', 'Insufficent Clean Clear');

            } elseif ($request->Green_Colour > $checkSort->Green_Colour) {
                return back()->with('error', 'Insufficent Green Colour');
            } elseif ($request->Others > $checkSort->Others) {
                return back()->with('error', 'Insufficent Others');
            } elseif ($request->Trash > $checkSort->Trash) {
                return back()->with('error', 'Insufficent Trash');
            }

            $saledetails = new SalesDetails();
            $saledetails->Clean_Clear = $request->Clean_Clear ?? 0;
            $saledetails->Green_Colour = $request->Green_Colour ?? 0;
            $saledetails->Others = $request->Others ?? 0;
            $saledetails->Trash = $request->Trash ?? 0;
            $saledetails->location_id = $request->collection_id;
            $saledetails->customer_name = $request->customer_name;
            $saledetails->price_per_ton = $request->price_per_ton ?? 0;
            $saledetails->currency = $request->currency;
            if ($request->currency == "NGN") {
                $saledetails->amount_ngn = $request->amount ?? 0;
            }
            if ($request->currency == "USD") {
                $saledetails->amount_usd = $request->amount ?? 0;
            }
            $saledetails->user_id = Auth::id();
            $saledetails->save();

            $saledetails = ($saledetails->Clean_Clear + $saledetails->Others + $saledetails->Green_Colour + $saledetails->Trash);
            $total = Total::where('location_id', $request->collection_id)->first();
            $old_total_transfered = $total->transfered;
            $total->update(['bailed' => ($total->bailed - $saledetails)]);

            $updated = BailedDetails::where('location_id', $request->collection_id)->first();
            //dd($updated->Clean_Clear);
            $updated->update(['Clean_Clear' => ($updated->Clean_Clear - $request->Clean_Clear ?? 0)]);
            $updated->update(['Green_Colour' => ($updated->Green_Colour - $request->Green_Colour ?? 0)]);
            $updated->update(['Others' => ($updated->Others - $request->Others ?? 0)]);
            $updated->update(['Trash' => ($updated->Trash - $request->Trash ?? 0)]);

            $factory_id = $request->collection_id;
            $sales_amount = $request->amount ?? 0;
            if (FactoryTotal::where('location_id', $factory_id)->exists()) {
                # code...
                $t = FactoryTotal::where('location_id', $factory_id)->first();
                $t->update(['sales' => ($t->sales + $sales_amount)]);
            } else {
                $total = new FactoryTotal();
                $total->sales = $sales_amount;
                $total->location_id = $request->collection_id;
                $total->save();
            }

            return back()->with('message', 'Sales Successfully');
        } catch (Exception $e) {
            return back()->with('error', $e);
        }
    }
    public function collectionFilter(Request $request)
    {

        $start_date = Carbon::parse($request->start_date)
            ->toDateTimeString();

        $end_date = Carbon::parse($request->end_date)
            ->toDateTimeString();

        $collection = Collection::orWhereBetween('created_at', [
            $start_date, $end_date,
        ])->orWhere('location_id', $request->location_id)
            ->paginate(50);
        $location = Location::all();
        return view('collection_report', compact('collection', 'location'));
    }

    public function collection_filter()
    {
        $collection = Collection::paginate(50);
        $location = Location::all();
        return view('collection_report', compact('collection', 'location'));
    }

    public function sortedFilter(Request $request)
    {
        $start_date = Carbon::parse($request->start_date)
            ->toDateTimeString();

        $end_date = Carbon::parse($request->end_date)
            ->toDateTimeString();

        $sorting = Sorting::orWhereBetween('created_at', [
            $start_date, $end_date,
        ])->orWhere('location_id', $request->location)->paginate(50);
        $collection = Location::all();
        return view('sorting_report', compact('sorting', 'collection'));
    }

    public function sorted_filter()
    {
        $sorting = Sorting::paginate(50);
        $collection = Location::all();
        return view('sorting_report', compact('sorting', 'collection'));
    }

    public function bailedFilter(Request $request)
    {
        $start_date = Carbon::parse($request->start_date)
            ->toDateTimeString();

        $end_date = Carbon::parse($request->end_date)
            ->toDateTimeString();

        $bailed = Bailing::orWhereBetween('created_at', [
            $start_date, $end_date,
        ])->orWhere('location_id', $request->location)->paginate(50);
        $collection = Location::all();
        return view('bailed_report', compact('bailed', 'collection'));
    }

    public function bailed_filter()
    {
        $bailed = Bailing::paginate(50);
        $collection = Location::all();
        return view('bailed_report', compact('bailed', 'collection'));
    }

    public function transferFilter(Request $request)
    {
        $start_date = Carbon::parse($request->start_date)
            ->toDateTimeString();

        $end_date = Carbon::parse($request->end_date)
            ->toDateTimeString();

        $transfered = Transfer::orWhereBetween('created_at', [
            $start_date, $end_date,
        ])->orWhere('location_id', $request->location)
            ->orWhere('factory_id', $request->factory)
            ->paginate(50);
        $collection = Location::all();
        $result = 0;
        $factory = Factory::all();
        return view('transfered_report', compact('transfered', 'collection', 'factory'));
    }

    public function transfer_filter()
    {
        $result = 0;
        $transfered = Transfer::paginate(50);
        $collection = Location::all();
        $factory = Factory::all();
        return view('transfered_report', compact('transfered', 'collection', 'factory'));
    }

    public function recycleFilter(Request $request)
    {
        $start_date = Carbon::parse($request->start_date)
            ->toDateTimeString();

        $end_date = Carbon::parse($request->end_date)
            ->toDateTimeString();

        $recycled = Recycle::orWhereBetween('created_at', [
            $start_date, $end_date,
        ])->orWhere('factory_id', $request->factory)
            ->paginate(50);
        $collection = Location::all();
        $factory = Factory::all();
        return view('recycled_report', compact('recycled', 'collection', 'factory'));
    }

    public function recycle_filter()
    {
        $recycled = Recycle::paginate(50);
        $collection = Location::all();
        $factory = Factory::all();
        return view('recycled_report', compact('recycled', 'collection', 'factory'));
    }

    public function salesFilter(Request $request)
    {
        $start_date = Carbon::parse($request->start_date)
            ->toDateTimeString();

        $end_date = Carbon::parse($request->end_date)
            ->toDateTimeString();

        $sales = Sales::orWhereBetween('created_at', [
            $start_date, $end_date,
        ])
            ->orWhere('factory_id', 'like', '%' . $request->factory . '%')
            ->paginate(50);
        $collection = Location::all();
        $factory = Factory::all();
        return view('sales_report', compact('sales', 'collection', 'factory'));
    }

    public function sales_filter()
    {
        $sales = Sales::paginate(50);
        $collection = Location::all();
        $factory = Factory::all();
        return view('sales_report', compact('sales', 'collection', 'factory'));
    }

    public function salesBailedFilter(Request $request)
    {
        $start_date = Carbon::parse($request->start_date)
            ->toDateTimeString();

        $end_date = Carbon::parse($request->end_date)
            ->toDateTimeString();

        $sales = SalesDetails::orWhereBetween('created_at', [
            $start_date, $end_date,
        ])
            ->orWhere('location_id', 'like', '%' . $request->location_id . '%')
            ->paginate(50);
        $collection = Location::where('type', 'c')->get();
        $factory = Factory::all();
        return view('salesbailed_report', compact('sales', 'collection', 'factory'));
    }

    public function salesbailed_filter()
    {
        $sales = SalesDetails::paginate(50);
        $collection = Location::where('type', 'c')->get();
        $factory = Factory::all();
        return view('salesbailed_report', compact('sales', 'collection', 'factory'));
    }

    public function sortedTransfer(Request $request)
    {
        try {
            $result = ($request->Clean_Clear + $request->Others + $request->Green_Colour + $request->Trash + $request->Caps);
            // $t = SortDetails::where('location_id', $request->toLocation)->first();
            // if(empty($t)){
            //     return back()->with('error','No Record Found');

            // }
            $t = SortDetails::where('location_id', $request->fromLocation)->first();
            if (empty($t)) {
                return back()->with('error', 'No Record Found');

            }
            $tsorted = ($t->Clean_Clear + $t->Others + $t->Green_Colour + $t->Trash + $t->Caps);
            if ($result > $tsorted) {
                return back()->with('error', 'Insufficent Sorted ');
            }
            $checkSort = SortDetails::where('location_id', $request->fromLocation)->first();
            if (empty($checkSort)) {
                return back()->with('error', 'No Collection Found');
            }
            if ($request->Clean_Clear > $checkSort->Clean_Clear) {

                return back()->with('error', 'Insufficent Clean Clear');

            } elseif ($request->Green_Colour > $checkSort->Green_Colour) {
                return back()->with('error', 'Insufficent Green Colour');
            } elseif ($request->Others > $checkSort->Others) {
                return back()->with('error', 'Insufficent Others');
            } elseif ($request->Trash > $checkSort->Trash) {
                return back()->with('error', 'Insufficent Trash');
            } elseif ($request->Caps > $checkSort->Caps) {
                return back()->with('error', 'Insufficent Trash');
            }

            $sortedTransfer = new SortedTransfer();
            $sortedTransfer->item_id = $request->item_id;
            $sortedTransfer->Clean_Clear = $request->Clean_Clear ?? 0;
            $sortedTransfer->Green_Colour = $request->Green_Colour ?? 0;
            $sortedTransfer->Others = $request->Others ?? 0;
            $sortedTransfer->Trash = $request->Trash ?? 0;
            $sortedTransfer->Caps = $request->Caps ?? 0;
            $sortedTransfer->formLocation = $request->fromLocation ?? 0;
            $sortedTransfer->toLocation = $request->toLocation ?? 0;
            $sortedTransfer->location_id = Auth::user()->location_id;
            $sortedTransfer->user_id = Auth::id();
            //dd($sortedTransfer);
            $sortedTransfer->save();

            $t2 = SortDetails::where('location_id', $request->toLocation)->first();
            if (empty($t2)) {
                $sorted = new SortDetails();
                $sorted->Clean_Clear = $request->Clean_Clear ?? 0;
                $sorted->Green_Colour = $request->Green_Colour ?? 0;
                $sorted->Others = $request->Others ?? 0;
                $sorted->Trash = $request->Trash ?? 0;
                $sorted->Caps = $request->Caps ?? 0;
                $sorted->location_id = $request->toLocation;
                $sorted->user_id = Auth::id();
                $sorted->save();

            } else {

                $updated = SortDetails::where('location_id', $request->toLocation)->first();
                $updated->update(['Clean_Clear' => ($updated->Clean_Clear + $request->Clean_Clear ?? 0)]);
                $updated->update(['Green_Colour' => ($updated->Green_Colour + $request->Green_Colour ?? 0)]);
                $updated->update(['Others' => ($updated->Others + $request->Others ?? 0)]);
                $updated->update(['Trash' => ($updated->Trash + $request->Trash ?? 0)]);
                $updated->update(['Caps' => ($updated->Caps + $request->Caps ?? 0)]);
            }

            $updated = SortDetails::where('location_id', $request->fromLocation)->first();
            $updated->update(['Clean_Clear' => ($updated->Clean_Clear - $request->Clean_Clear ?? 0)]);
            $updated->update(['Green_Colour' => ($updated->Green_Colour - $request->Green_Colour ?? 0)]);
            $updated->update(['Others' => ($updated->Others - $request->Others ?? 0)]);
            $updated->update(['Trash' => ($updated->Trash - $request->Trash ?? 0)]);
            $updated->update(['Caps' => ($updated->Caps - $request->Caps ?? 0)]);

            return back()->with('message', 'Transfer Successfully');

        } catch (Exception $e) {
            return back()->with('error', $e);
        }
    }

    public function sortedTransferView(Request $request)
    {
        $item = Item::all();
        $collection = Location::where('type', 'c')->get();
        $sortedTransfer = SortedTransfer::orderBy('created_at', 'desc')->get();
        return view('sortedtransfer', compact('sortedTransfer', 'collection', 'item'));
    }

    public function forgot_pin(Request $request)
    {

        $email = $request->email;

        return view('forgotpin', compact('email'));

    }

    public function forgot_pin_now(Request $request)
    {

        $email = $request->email;

        $input = $request->validate([
            'password' => ['required', 'confirmed', 'string', 'min:1', 'max:4'],
        ]);

        $update = User::where('email', $email)
            ->update([
                'pin' => Hash::make($request->password),
            ]);

        return view('pin-success');

    }

    public function forgot_password(Request $request)
    {

        $email = $request->email;

        return view('forgotemail', compact('email'));

    }

    public function forgot_password_now(Request $request)
    {

        $email = $request->email;

        $input = $request->validate([
            'password' => ['required', 'confirmed', 'string'],
        ]);

        $update = User::where('email', $email)
            ->update([
                'password' => Hash::make($request->password),
            ]);

        return view('success');

    }

    public function verify_email_code(Request $request)
    {

        return view('verify-email-code');

    }

    public function contact_us(Request $request)
    {

        return view('contact-us');

    }


    public function fund_account(request $request)
    {

        $amount = $request->amount;
        $trans_id = $request->trans_id;
        $status = $request->status;

        if($status == 'failed'){

            $accountfunds = Transaction::where('type', 'fund_account')
            ->get();

            return redirect('fund-account')->with('error', 'Trasnaction Failed');

        }

        if($status == 'success'){

            $accountfunds = Transaction::where('type', 'fund_account')
            ->get();

            $transaction = new Transaction();
            $transaction->trans_id = $trans_id;
            $transaction->amount = $amount;
            $transaction->user_id = Auth::id();
            $transaction->type = 'fund_account';
            $transaction->save();


            return redirect('fund-account')->with('message', 'Trasnaction Successful');

        }


        if($status == null){

            $accountfunds = Transaction::where('type', 'fund_account')
            ->get();

            return view('fund-account', compact('accountfunds'));

        }




    }



    public function fund_account_now(request $request)
    {


        $amount = $request->amount;
        $key = env('EKEY');
        $pay = "https://web.sprintpay.online/pay?amount=$amount&key=$key";

        return Redirect::to($pay);


    }


    public function webhook(request $request)
    {

        $amount = $request->amount;
        $key = env('EKEY');
        $pay = "https://web.sprintpay.online/pay?amount=$amount&key=$key";

        return Redirect::to($pay);


    }





}
