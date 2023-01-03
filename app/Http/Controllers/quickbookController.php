<?php

namespace App\Http\Controllers;

use App\Models\AccountingConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\DataService\DataService;

class quickbookController extends Controller
{
    public static $temp_id=null;
    public static $attemps=0;
    public function connect(Request $request)
    {
        Session::put('accounting_account',$request->business_name);
        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => config('myconfig.Quickbook.ci'),
            'ClientSecret' => config('myconfig.Quickbook.sk'),
            'RedirectURI' => route('quickbook.redirect_uri'),
            'scope' => "com.intuit.quickbooks.accounting",
            'baseUrl' => "Development/Production"
        ));
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $authorizationCodeUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
        return redirect($authorizationCodeUrl);
    }
    public function callback(Request $request)
    {
        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => config('myconfig.Quickbook.ci'),
            'ClientSecret' =>config('myconfig.Quickbook.sk'),
            'RedirectURI' => "https://account.zpayd.com/quickbook/redirect_url",
            'scope' => "com.intuit.quickbooks.accounting",
            'baseUrl' => "Development/Production"
        ));
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($request->code,$request->realmId);
        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();
        if (!AccountingConnection::where(['user_id'=>auth()->user()->id,'account_id'=>$request->realmId,'service_name'=>'Quickbooks','deleted_at'=>null])->where('status','connected')->exists())
        {
            AccountingConnection::where(['user_id'=>auth()->user()->id,'business_name'=>Session::get('accounting_account')])->update([
                'user_id'=>auth()->user()->id,
                'account_id'=>$request->realmId,
                'service_name'=>'Quickbooks',
                'access_token'=>$accessTokenValue,
                'status'=>'connected',
                'refresh_token'=>$refreshTokenValue
            ]);
            return redirect(route('accounting.index'))->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Connection successful.',
                    'type' => 'success',
                ]
            ]);
        }
        else
        {
            return redirect(route('accounting.index'))->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Connection Already Existed.',
                    'type' => 'success',
                ]
            ]);
        }
    }
    public static function sendTransaction($connection_id,$vendorName,$issue_date,$amount,$due_offset_days=0,$currency_code='CAD',$language='en',$categoryid='4')
    {
        $b=AccountingConnection::find($connection_id);
        self::$temp_id=null;
        self::$attemps=0;
        self::createVendor($vendorName,$connection_id);
        if (self::$temp_id!=null)
        {

            $url=config('myconfig.Quickbook.url').'/v3/company/'.$b->account_id.'/bill?minorversion='.config('myconfig.Quickbook.ver');

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
    "Line":[
        {
            "Id":"1",
            "Amount":'.floatval($amount).',
            "DetailType":"AccountBasedExpenseLineDetail",
            "AccountBasedExpenseLineDetail":
            {
                "AccountRef":
                {
                    "value":"'.$categoryid.'"
                }
            }
        }
    ],
    "VendorRef":
    {
        "value":'.self::$temp_id.'
    }
}',
                CURLOPT_HTTPHEADER => array(
                    'User-Agent: QBOV3-OAuth2-Postman-Collection',
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Bearer '.$b->access_token
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
//           dd($response);

        }


    }
    public static function createVendor($display_name,$connection_id,$currency_code='CAD',$language='en')
    {
        $b=AccountingConnection::find($connection_id);
        $url=config('myconfig.Quickbook.url').'/v3/company/'.$b->account_id.'/vendor?minorversion='.config('myconfig.Quickbook.ver');

        $response=Http::withToken($b->access_token)->post($url,[
            'DisplayName'=>$display_name
        ]);

        if ($response->failed())
        {

            $response=$response->json();

            $errors=$response['Fault']['Error'];
            if ($errors[0]['code']=='6240')
            {
                self::getVendorByName($connection_id,$display_name);
            }

        }
        else
        {
            self::getVendorByName($connection_id,$display_name);
        }


//asas
    }
    public static function getVendorByName($connection_id,$name,$page=1)
    {
        self::$temp_id=null;
        $b=AccountingConnection::find($connection_id);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://quickbooks.api.intuit.com/v3/company/9130352999865296/query?minorversion=14',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'select * from vendor ',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/text',
                'Authorization: Bearer '.$b->access_token
            ),
        ));

        $response = curl_exec($curl);

        $response=json_decode($response);
        $vendors=$response->QueryResponse->Vendor;


        foreach ($vendors as $v)
        {
            if ($v->DisplayName==$name)
            {
                self::$temp_id=$v->Id;

            }
        }
//        $url=config('myconfig.Quickbook.url').'/v3/company/'.$b->account_id.'/query?minorversion='.config('myconfig.Quickbook.ver');
//
//        $response=Http::withToken($b->access_token)->post($url,['select * from vendor']);
//        dd($response->json());
    }
    public static function refreshToken($connection_id)
    {
        $b=AccountingConnection::find($connection_id);
        $oauth2LoginHelper = new OAuth2LoginHelper(config('myconfig.Quickbook.ci'),config('myconfig.Quickbook.sk'));
        $accessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($b->refresh_token);
        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();
        $b->status="connected";
        $b->access_token=$accessTokenObj->getAccessToken();
        $b->refresh_token=$accessTokenObj->getRefreshToken();
        $b->save();



    }
    public static function checkTokenIsValid($connection_id,$count=0)
    {
        $b=AccountingConnection::find($connection_id);
        $url=config('myconfig.Quickbook.url').'/v3/company/'.$b->account_id.'/query?minorversion='.config('myconfig.Quickbook.ver');

        $response=Http::withToken($b->access_token)->get($url);
        if ($response->status()==401)
        {
            $b->status='expired';
            $b->save();
            self::refreshToken($connection_id);
        }

    }
}
