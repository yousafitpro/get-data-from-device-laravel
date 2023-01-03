<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\MetaController;
use App\Http\Controllers\Api\UserApplicationController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\LenderApplicationController;
use App\Http\Controllers\Api\ProfileController;

Route::group([

    'middleware' => 'auth:api',
    'prefix' => 'note'

], function ($router) {

    Route::get('get', [\App\Http\Controllers\noteController::class,'getUserNote']);
    Route::get('set', [\App\Http\Controllers\noteController::class,'setUserNote']);

});
Route::group([

    'middleware' => 'auth:api',
    'prefix' => 'alert'

], function ($router) {

    Route::any('myalerts', [\App\Http\Controllers\AlertController::class,'myAlerts']);
    Route::get('open/{id}', [\App\Http\Controllers\AlertController::class,'opened']);

});
Route::prefix('square')
    ->group(function () {

        Route::any('webhook-payment', [\App\Http\Controllers\sqaureController::class, 'webhook_payment'])->name('sqaure.webhook_payment');

    });
Route::group([

    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('register', 'AuthController@register');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('me', 'AuthController@me');

});
Route::group([

    'middleware' => 'auth:api',
    'namespace' => 'App\Http\Controllers\Backend',
    'prefix' => 'dashboard'

], function ($router) {

    Route::get('index', 'DashboardController@index');

});
Route::prefix('plaid')
    ->middleware(['auth:api'])
    ->group(function () {


        Route::any('get-link-token', [\App\Http\Controllers\plaidController::class, 'Get_Link_Token'])->name('plaid.getLinkToken');
        Route::any('get-access-token', [\App\Http\Controllers\plaidController::class, 'Get_Access_Token'])->name('plaid.getAccessToken');

    });
Route::prefix('loc/accounts')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('remove/{id}', [\App\Http\Controllers\LocBankAccountController::class, 'remove']);
        Route::get('set_primary/{id}', [\App\Http\Controllers\LocBankAccountController::class, 'set_primary']);

    });
Route::prefix('main/accounts')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('remove/{id}', [\App\Http\Controllers\BankAccountController::class, 'remove']);
        Route::get('set_primary/{id}', [\App\Http\Controllers\BankAccountController::class, 'set_primary']);

    });

Route::prefix('v1')
    ->group(function () {



        Route::post('postOffer', [\App\Http\Controllers\ApplicationofferController::class,'postOffer']);

        Route::get('getApplications', [UserApplicationController::class,'getApplications']);
         // webhooks
        Route::any('receive-payment-order-status', [\App\Http\Controllers\webhookController::class, 'receive_payment_order_status']);




        Route::post('registration', [RegisterController::class, 'store']);
        Route::post('login', [LoginController::class, 'postLogin']);
        Route::get('plans', [MetaController::class, 'planList']);
        Route::get('bill-vendors', [MetaController::class, 'billVendorList']);
        Route::post('service-suggestion', [MetaController::class, 'serviceSuggestion']);
        Route::resource('bills', BillController::class);
        Route::resource('applications', UserApplicationController::class);
        Route::resource('transactions', TransactionController::class);

        Route::get('user-detail', [MetaController::class, 'userDashboard']);
        Route::get('lender-detail', [MetaController::class, 'lenderDashboard']);
        Route::get('paid-bills', [MetaController::class, 'userPaidBills']);
        Route::get('my-users', [MetaController::class, 'myUsers']);

        Route::get('new-applications', [LenderApplicationController::class, 'newApplications']);
        Route::get('my-applications', [LenderApplicationController::class, 'myApplications']);
        Route::post('new-applications', [LenderApplicationController::class, 'update']);

//        Route::post('profile', [ProfileController::class, 'profileUpdate']);
//        Route::post('change-password', [ProfileController::class, 'changePasswordPost']);

    });
Route::prefix('applications')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::post('add', [\App\Http\Controllers\ApplicationController::class, 'store']);
        Route::get('list', [\App\Http\Controllers\ApplicationController::class, 'list']);

    });

Route::prefix('bills')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::post('add', [\App\Http\Controllers\BillController::class, 'store']);
        Route::get('list', [\App\Http\Controllers\BillController::class, 'list']);
        Route::get('paid-list', [\App\Http\Controllers\BillController::class, 'paid_list']);
        Route::get('myBills', [\App\Http\Controllers\BillController::class, 'myBills']);
        Route::get('delete-bill/{id}', [\App\Http\Controllers\BillController::class, 'destroy']);
        Route::get('cancel-bill/{id}', [\App\Http\Controllers\BillController::class, 'cancel_bill']);

    });
// contact

Route::prefix('contact')
    ->middleware('auth:api')
    ->group(function () {
        Route::post('add', [\App\Http\Controllers\ContactController::class, 'add'])->name('contact.add');
        Route::post('update/{id}', [\App\Http\Controllers\ContactController::class, 'update'])->name('contact.update');
        Route::get('list', [\App\Http\Controllers\ContactController::class, 'list'])->name('contact.list');
        Route::get('remove/{id}', [\App\Http\Controllers\ContactController::class, 'remove'])->name('contact.remove');
        Route::get('remainingUsers', [\App\Http\Controllers\ContactController::class, 'remainingUsers']);
    });


// payee

Route::prefix('payee')
    ->middleware('auth:api')
    ->group(function () {
        Route::post('add', [\App\Http\Controllers\PayeeController::class, 'add'])->name('payee.add');
        Route::post('add-my-payee', [\App\Http\Controllers\PayeeController::class, 'add_my_payee'])->name('payee.addMyPayee');
        Route::get('remove-my-payee/{id}', [\App\Http\Controllers\PayeeController::class, 'remove_my_payee'])->name('payee.removeMyPayee');
        Route::get('list', [\App\Http\Controllers\PayeeController::class, 'mypayees'])->name('payee.providers');


        Route::get('providers', [\App\Http\Controllers\PayeeController::class, 'providers'])->name('payee.providers');

    });
// share bills
Route::prefix('shared-bill')
    ->middleware('auth:api')
    ->group(function () {
        Route::post('add', [\App\Http\Controllers\SharedBillController::class, 'add']);
        Route::get('edit/{id}', [\App\Http\Controllers\SharedBillController::class, 'edit'])->name('sharedBill.edit');
        Route::post('update/{id}', [\App\Http\Controllers\SharedBillController::class, 'update'])->name('sharedBill.update');
        Route::post('add-member', [\App\Http\Controllers\SharedBillController::class, 'addMember'])->name('sharedBill.addMember');
        Route::get('remove-member/{id}', [\App\Http\Controllers\SharedBillController::class, 'removeMember'])->name('sharedBill.removeMember');
        Route::get('complete/{id}', [\App\Http\Controllers\SharedBillController::class, 'complete'])->name('sharedBill.complete');
        Route::get('list', [\App\Http\Controllers\SharedBillController::class, 'sharedBills'])->name('sharedBill.list');
        Route::get('requests', [\App\Http\Controllers\Backend\DashboardController::class, 'payment_requests'])->name('dashboard.paymentRequests');
        Route::post('member-confirm/{id}', [\App\Http\Controllers\SharedBillController::class, 'memberConfirm'])->name('sharedBill.memberConfirm');

    });
Route::prefix('profile')
    ->middleware('auth:api')
    ->group(function () {
        Route::post('update', [\App\Http\Controllers\ProfileController::class, 'store']);
        Route::post('change-password', [\App\Http\Controllers\ProfileController::class, 'changePasswordPost']);
    });
Route::prefix('Utilities')
    ->middleware('auth:api')
    ->group(function () {
        Route::get('dateData', [\App\Http\Controllers\UtilitiesController::class, 'dateData']);
    });
Route::prefix('zumrails')
    ->group(function () {
        Route::get('web_url', [\App\Http\Controllers\zumrailsController::class, 'web_url']);
    });

Route::middleware('auth:api')
    ->prefix('security')
    ->group(function () {
        Route::get('2FA',[\App\Http\Controllers\TwoStepVerificationController::class,'index'])->name('security.2FA');
        Route::get('email/2FA',[\App\Http\Controllers\TwoStepVerificationController::class,'email_2FA'])->name('security.2FA');

        Route::get('accept-agreement',[\App\Http\Controllers\SubscriptionController::class,'accept_agreement'])->name('security.acceptAgreement');
        Route::get('accept_agreement_now',[\App\Http\Controllers\SubscriptionController::class,'accept_agreement_now'])->name('security.acceptAgreementNow');
        Route::get('renew-package',[\App\Http\Controllers\SubscriptionController::class,'renew_package'])->name('security.renewPackage');
        Route::get('page',[\App\Http\Controllers\SecurityController::class,'page'])->name('security.page');
        Route::get('disable-2FA',[\App\Http\Controllers\SecurityController::class,'disable_2fa'])->name('security.disable2fa');
        Route::post('verify-phone-number',[\App\Http\Controllers\SecurityController::class,'verify_phone_number'])->name('security.verifyPhoneNumber');
        Route::post('Verify2FACode',[\App\Http\Controllers\TwoStepVerificationController::class,'Verify2FACode'])->name('security.Verify2FACode');
        Route::post('VerifyEmail2FACode',[\App\Http\Controllers\TwoStepVerificationController::class,'VerifyEmail2FACode'])->name('security.VerifyEmail2FACode');

        Route::post('verify-phone-number-step-2',[\App\Http\Controllers\SecurityController::class,'verify_phone_number_step_2'])->name('security.verifyPhoneNumberStep_2');
    });
Route::middleware('auth:api')
    ->prefix('telpay')
    ->group(function () {
        Route::get('servicesList', [\App\Http\Controllers\talpayController::class, 'ServicesList']);
        Route::get('validateAccountNumber', [\App\Http\Controllers\talpayController::class, 'validateAccountNumber']);

    });

Route::middleware('auth:api')
    ->prefix('user')
    ->group(function () {
        Route::get('settings',[\App\Http\Controllers\UserSettingController::class,'index']);
    });
Route::prefix('support')
    ->middleware('auth:api')
    ->group(function () {
        Route::post('create-query', [\App\Http\Controllers\SupportquaryController::class, 'create_query'])->name('support.createQuery');

    });
Route::prefix('Banking')
    ->middleware('auth:api')
    ->group(function () {
        Route::get('e-transfer-detail', [\App\Http\Controllers\EtransferController::class, 'e_transfer_detail'])->name('banking.eTransferDetail');
        Route::get('e-transfer/all-transactions', [\App\Http\Controllers\EtransferController::class, 'e_transfer_all_transactions'])->name('banking.eTransferAllTransactions');
        Route::post('e-transfer/add-new-transaction', [\App\Http\Controllers\EtransferController::class, 'e_transfer_add_new_transaction'])->name('banking.eTransferAddNewTransaction');
        Route::post('e-transfer/update-transaction/{id}', [\App\Http\Controllers\EtransferController::class, 'e_transfer_update_transaction'])->name('banking.eTransferUpdateTransaction');

    });
Route::middleware('auth:api')
    ->prefix('setting/notifications')
    ->group(function () {
        Route::get('index',[\App\Http\Controllers\settingController::class,'index'])->name("setting.notification.index");
        Route::post('update-column',[\App\Http\Controllers\settingController::class,'update_column'])->name("setting.notification.update_column");
    });
Route::prefix('railz')
    ->group(function () {
        Route::any('new_connection', [\App\Http\Controllers\railzController::class, 'new_connection_web_hook']);
        Route::any('disconnect_connection', [\App\Http\Controllers\railzController::class, 'disconnect_connection_web_hook']);
        Route::any('push_status', [\App\Http\Controllers\railzController::class, 'push_status_web_hook']);

    //ASa
    });
Route::post('payee/save_suggested_payee',[\App\Http\Controllers\PayeeController::class,'save_suggested_payee']);
include('api/freshbook.php');
include('api/fund.php');
include('api/addfund.php');
include('api/user.php');
include('api/aptpay.php');
include('api/offer.php');


Route::any('test-me/{id}',function (Request $request,$id){


    // dd($request->image);
    // $filePath='https://merchant.zpayd.com/images/dcard.png';
    // $d=new \CurlFile($filePath, 'image/png', 'filename.png');
    // dd($d);
    
    $filePath='https://merchant.zpayd.com/images/dcard.png';

    $aPostData = [
        'identificationType' => 'DRIVERS_LICENSE',
        'identificationNumber' => 'R0336-28208-50212',
        'identificationDate' => '2020-03-12',
        'identificationDateOfExpiration' => '2026-02-12',
        'identificationLocation' => 'Toronto, Canada',
        'virtual' => 1,
    ];
    $ch = curl_init( Config::get('myconfig.AP.url').'/identities/'.$id.'/kyc' );
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: multipart/form-data',
        'AptPayApiKey: '.Config::get('myconfig.AP.key')
    ));

    $sBodyHash = hash_hmac('sha512', http_build_query( $aPostData ), Config::get('myconfig.AP.secret') );
    var_dump( $sBodyHash );
    $aPostData['identificationFile'] = $request->image;


    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'AptPayApiKey: '.Config::get('myconfig.AP.key'), 'body-hash: '.$sBodyHash] );
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $aPostData );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $sRes = curl_exec($ch);
    dd($sRes);
    exit;
});
