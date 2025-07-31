<?php

use App\Http\Controllers\BroadcastAvailableDrivers;
use App\Http\Controllers\WasteBillController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthCoontroller;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\SortingController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\FactoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\BailingController;
use App\Http\Controllers\RecycleController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SortedTransferController;
use App\Http\Controllers\TransactionController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

    //forgetpassword
    Route::post('forgot-password', [AuthCoontroller::class, 'forgot_password']);


    //forgetpassword
    Route::post('forgot-pin', [AuthCoontroller::class, 'forgot_pin']);



Route::post('verify-bank-account', [TransactionController::class, 'verify_bank_account']);
   // customer settings
Route::get('get-slider', [SettingController::class, 'get_slider']);

Route::any('e-fund', [WasteBillController::class, 'ProcessPaymentBill']);

Route::post('get-bill-pdf', [WasteBillController::class, 'GetWasteBillPdf']);
Route::post('scan-code', [WasteBillController::class, 'ScanCode']);
Route::post('get-customer-name', [WasteBillController::class, 'GetCustomerName']);
Route::get('get-customer-order', [WasteBillController::class, 'GetCustomerOrder']);
Route::post('get-list-product', [WasteBillController::class, 'GetList']);





Route::middleware('auth:sanctum', 'access')->get('/user', function (Request $request) {
    return $request->user();
});
//users
Route::post('createUser', [AuthCoontroller::class, 'register']);
Route::post('updatepassword', [AuthCoontroller::class,'updateUser']);
Route::post('login', [AuthCoontroller::class, 'login']);


Route::post('verify-email', [AuthCoontroller::class, 'verify_email']);
Route::post('resend-code-email', [AuthCoontroller::class, 'resend_code']);
Route::post('update-email', [AuthCoontroller::class, 'update_email']);
Route::post('verify-sms-code', [AuthCoontroller::class, 'verify_sms_code']);












Route::group(['middleware' => ['auth:api','access']], function(){

//pin login
Route::post('pin-login', [AuthCoontroller::class, 'pin_login']);


//create and get collection
Route::post('collection', [CollectionController::class, 'collect']);
Route::get('getCollection', [CollectionController::class, 'getCollection']);


Route::get('driver-properties', [WasteBillController::class, 'DriverProperties']);
Route::get('get-bulk-list', [WasteBillController::class, 'GetBulkList']);
Route::get('validate-customer', [WasteBillController::class, 'CustomerValidation']);
Route::get('driver-collect-waste', [WasteBillController::class, 'CollectBulkWaste']);



//create and get sorting
Route::post('sorting', [SortingController::class, 'sorted']);
Route::get('getSorting', [SortingController::class, 'getSorted']);
Route::get('getUnSorted', [SortingController::class, 'getUnSorted']);


//create and get sorting
Route::post('transfer-sorted-loose', [SortedTransferController::class, 'sortedTransfer']);
Route::post('transfer-unsorted-loose', [SortedTransferController::class, 'unsortedTransfer']);

Route::get('getsortingtransfer', [SortedTransferController::class, 'getSortedTransfer']);

//create and get location
Route::post('location', [LocationController::class, 'location']);
Route::get('getLocation', [LocationController::class, 'getLocation']);
Route::get('getfactory', [LocationController::class, 'getfactory']);



//create and get bailing
Route::post('bailing', [BailingController::class, 'bailing']);
Route::get('getBailing', [BailingController::class, 'getBailing']);
Route::get('getUnsortedBailing', [BailingController::class, 'getUnsortedBailing']);
Route::post('transfer-sorted-bailed', [BailingController::class, 'transfer_sorted_bailed']);
Route::post('transfer-unsorted-bailed', [BailingController::class, 'transfer_unsorted_bailed']);



    Route::post('customer-bulk-drop', [WasteBillController::class, 'CustomerBulkDrop']);









//create and get factory
Route::post('factory', [FactoryController::class, 'factory']);
Route::get('getFactory', [FactoryController::class, 'getFactory']);


//create, get and update  transfer
Route::post('transfer', [TransferController::class, 'transfer']);
Route::get('getTransfer', [TransferController::class, 'getTransfer']);
Route::get('getTransferHistory', [TransferController::class, 'getTransferHistory']);


Route::post('updateTransfer', [TransferController::class, 'updateTransfer']);

//get history
Route::get('getHistory', [TransferController::class, 'history']);

//get all items
Route::get('bailingList', [ItemsController::class, 'bailingList']);
Route::get('itemList', [ItemsController::class, 'itemList']);


//create and get sales
Route::post('sales', [SalesController::class, 'sales']);
Route::get('getSales', [SalesController::class, 'getSales']);
Route::post('getsalesbrakedown', [SalesController::class, 'getSalesbrakedown']);
Route::post('saleBailed', [SalesController::class, 'saleBailed']);


//create and get recycle
Route::post('recycle', [RecycleController::class, 'recycle']);
Route::get('getRecycle', [RecycleController::class, 'getRecycle']);

Route::post('deviceId', [AuthCoontroller::class, 'deviceId']);




});


//create customer

Route::post('customer-register', [AuthCoontroller::class, 'customer_register']);
Route::get('get-banks', [TransactionController::class, 'get_banks']);
Route::get('all-state', [CollectionController::class, 'all_state']);
Route::post('get-lga', [CollectionController::class, 'get_lga']);
Route::post('fetch-account', [TransactionController::class, 'fetch_account']);






Route::post('customer-login', [AuthCoontroller::class, 'customer_login']);








//customer operations

Route::group(['middleware' => ['auth:api','access']], function(){

    //Waste Bills
    Route::get('get-waste-bills', [WasteBillController::class, 'GetBill']);
    Route::post('pay-bill', [WasteBillController::class, 'PayWasteBill']);








    Route::post('drop-off', [CollectionController::class, 'drop_off']);
    Route::post('delete-drop-off', [CollectionController::class, 'delete_drop_off']);
    Route::post('sms-code', [AuthCoontroller::class, 'sms_email_code']);
    Route::get('customer-drop-off-list', [CollectionController::class, 'drop_off_list']);


    //Update Logtitude
    Route::post('get-drivers', [BroadcastAvailableDrivers::class, 'broadcastAvailableDrivers']);
    Route::post('update-location', [BroadcastAvailableDrivers::class, 'UpdateLocation']);
    Route::post('driver-status', [BroadcastAvailableDrivers::class, 'DriverStatus']);
    Route::post('update-driver-status', [BroadcastAvailableDrivers::class, 'UpdateDriverStatus']);



    //customer drop off
    Route::get('get-plastic-waste', [CollectionController::class, 'get_plastic_waste']);
    Route::post('update-dropoff', [CollectionController::class, 'update_drop_off']);
    Route::post('nearest-location', [CollectionController::class, 'nearest_location']);
    Route::post('get-location-by-state', [CollectionController::class, 'location_by_state']);
    Route::post('get-location-by-city', [CollectionController::class, 'location_by_city']);
    Route::post('get-location-by-lga', [CollectionController::class, 'location_by_lga']);








    //customer transactions
    Route::post('get-all-transacction', [TransactionController::class, 'get_all_transactions']);
    Route::post('bank-transfer', [TransactionController::class, 'bank_transfer']);
    Route::post('transaction-verify', [TransactionController::class, 'transaction_verify']);



    Route::get('get-rate', [TransactionController::class, 'get_rate']);


    // customer profile
    Route::post('verify-pin', [TransactionController::class, 'verify_pin']);
    Route::post('update-password', [AuthCoontroller::class,'updateUser']);
    Route::post('update-pin', [AuthCoontroller::class,'updatePin']);
    Route::post('update-account', [AuthCoontroller::class,'updateAccountDetails']);







    ///Agent
    Route::post('agent-register', [AuthCoontroller::class, 'agent_register']);
    Route::post('agent-status', [AuthCoontroller::class, 'agent_status']);

    Route::post('get-user', [AuthCoontroller::class, 'get_user']);

    Route::post('agent-waste-list', [CollectionController::class, 'agent_waste_list']);
    Route::post('agent-waste-list-update', [CollectionController::class, 'agent_waste_list_update']);
    Route::post('udpade-dropoff-weight', [CollectionController::class, 'update_dropoff_weight']);


    Route::post('agent-total-weight', [CollectionController::class, 'agent_total_weight']);
















 });





