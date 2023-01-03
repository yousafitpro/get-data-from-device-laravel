<?php


use App\Models\Merchant\merchantOffers;
use App\Notifications\globalMessage;
use App\Notifications\sendMerchantOffer;
use Illuminate\Support\Facades\Notification;
//asdasd
Route::get('test-add-user-balance/{amount}',function (\Illuminate\Http\Request $request,$amount){

$u=\App\Models\User::where('email',$request->email)->first();

    $u->wallet_balance=encrypt($amount);
    $u->save();


});
Route::get('test-page',function (){
    return view('test');
});
Route::any('test-me',function (){

    $filePath='https://merchant.zpayd.com/images/dcard.png';
    $d=new \CurlFile($filePath, 'image/png', 'filename.png');
    dd($d);
});
Route::any('test-data',function (\Illuminate\Http\Request $request){
    dd(decrypt($request->name));
});
Route::get('test/identity/add-individual', function (){
    $data=[
        "individual"=> true,
        "first_name"=> "Test",
        "last_name"=> "Testington",
        "street"=>"DONORA DR",
        "email"=>"t11299@gmail.com",
        "city"=> "Toronto",
        "zip"=> "M4B 1B3",
        "country"=> "CA",
        "dateOfBirth"=> "1980-04-04",
        "clientId"=>456];
   \App\Http\Controllers\aptPayController::add_identity($data,null);
});
Route::get('test/validate-iban/{id}', function ($id){

    \App\Http\Controllers\aptPayController::validate_bank($id);
});
Route::get('test/send-inter', function (\Illuminate\Http\Request $request){

    \App\Http\Controllers\aptPayController::sendPaymentInternationalNow($request);
});
Route::get('test/xb-calculate', function (\Illuminate\Http\Request $request){

    \App\Http\Controllers\aptPayController::xb_calculate($request);
});
Route::get('test/add-kyc/{id}',function ($id){
    \App\Http\Controllers\aptPayController::addKYC($id);
});
Route::get('test/card-count',function (){
    // 4506445518387178
    dd(today_date());
});
Route::get('test/sender', function (){

    $data=[
        "individual"=> true,
        "first_name"=> "Gezim",
        "last_name"=> "Ramabaja",
        "street"=>"2325 hurontario street ",
        "email"=>"gzim.ramabaja@gmail.com",
        "city"=> "Mississauga ",
        "zip"=> "L5A 4K4",
        "country"=> "CA",
        "dateOfBirth"=> "1985-02-12",
        "typeOfId"=>"BUSINESS_ID",
        "idNumber"=>"GV6885352",
        "expirationDate"=>"2023-09-18",
        "nationality"=>"Canadian",
        "phone"=>"+38348166607",
        "clientId"=>45612
    ];
    \App\Http\Controllers\aptPayController::add_identity($data,null);
});
Route::get('test/add-b', function (){
    $data=[
        "individual"=> false,
        "first_name"=> "Gezim",
        "name"=>'Muhammad Yousaf',
        "last_name"=> "Ramabaja",
        "street"=>"2325 hurontario street ",
        "email"=>"gzim.ramabaja@gmail.com",
        "city"=> "Mississauga ",
        "zip"=> "L5A 4K4",
        "country"=> "CA",
        "dateOfBirth"=> "1985-02-12",
        "typeOfId"=>"BUSINESS_ID",
        "idNumber"=>"GV6885352",
        "expirationDate"=>"2023-09-18",
        "nationality"=>"Canadian",
        "phone"=>"+38348166607",
        "clientId"=>45612
    ];
    \App\Http\Controllers\aptPayController::add_identity($data,null);
});
Route::get('test/receiver', function (\Illuminate\Http\Request $request){


    $data=[
        "first_name"=> "Muhammad ",
        "last_name"=> "Yousaf",
        "street"=>$request->street,
        "address"=>$request->address,
        "email"=>"yousaf.itpro@gmail.com",
        "city"=> $request->city,
        "state"=> $request->state,
        "zip"=> $request->zip,
        "country"=> $request->country,
        "province"=>$request->province,
        "dateOfBirth"=> "1980-04-04",
        "typeOfId"=>456,
        "idNumber"=>"32202-2700894-9",
        "expirationDate"=>"2022-09-18",
        "nationality"=>"US",
        "phone"=>"+923170773093",
        "clientId"=>456
    ];

    \App\Http\Controllers\aptPayController::add_identity($data,null);
});
Route::get('test/identity/add-business', function (){

    \App\Http\Controllers\aptPayController::registerOnLogin(auth()->user());
});
Route::get('test/send-eft-debit/{id}', function ($id){

    \App\Http\Controllers\aptPayController::createEftDebit($id);
});
Route::get('test/sendOffer', function (){
    $data['user_id']=auth()->user()->id;
    $data['transaction_id']=random_int(100000,9000000);
    $data['transaction_date']=today_date();
    $data['status']="sent";
    $data['amount']="10";
    $data['commission']=auth()->user()->company->commission;
    $data['user']=auth()->user();
    $data['payurl']=route('merchant.offers.chargeCard',"1212");
    $data['companyName']=auth()->user()->company->short_name;
    Notification::route('mail', "yousaf.itpro@gmail.com")->notify(new sendMerchantOffer($data));
});
Route::get('test/identity/request-pay', function (){
    \App\Http\Controllers\aptPayController::request_pay(517416313723,12.0);
});
Route::get('test/call-webhook', function (){
    \App\Http\Controllers\aptPayController::registerWebhook();
});
Route::get('test/banks', function (){

    \App\Http\Controllers\aptPayController::get_banks("AT");
});
Route::get('test/branches/{id}', function ($id){

    \App\Http\Controllers\aptPayController::get_branches($id);
});
Route::get('test/type-of-ids/{id}', function ($id){

    \App\Http\Controllers\aptPayController::get_type_of_ids($id);
});
Route::get('test/cities/{id}', function ($id){

    \App\Http\Controllers\aptPayController::get_cities($id);
});
Route::get('test/identity/{id}', function ($id){

    \App\Http\Controllers\aptPayController::get_identity($id);
});
Route::get('test/purposes', function (){

    \App\Http\Controllers\aptPayController::get_purposes("AT");
});
Route::get('test/sendPay', function (){
//aasdasd
    \App\Http\Controllers\aptPayController::sendPay();
});
Route::get('test/disbursements', function (){

    \App\Http\Controllers\aptPayController::get_disbursements(auth()->user()->aptpay_identity);
});
Route::get('test/assign-role', function (){
$user=\App\Models\User::where('email','yousaf.itpro@gmail.com')->first();
$user->assignRole("company");
});
Route::get('test/event', function (){
    $data['offer']=merchantOffers::where('id','!=','2323')->first();
    \Illuminate\Support\Facades\Mail::to(['yousaf.itpro@gmail.com'])->send(new \App\Mail\offerPaymentConfirmationForAdmin($data));
//    event(new \App\Events\clientSubmittedThePaymentEvent($data));
});
Route::get('test/get_country_name', function (){
dd(get_country_name_by_code('US'));
});
