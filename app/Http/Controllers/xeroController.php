<?php

namespace App\Http\Controllers;

use App\Models\AccountingConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class xeroController extends Controller
{
    public static $temp_id=null;
    public static $attemps=0;
    public function connect(Request $request)
    {
        Session::put('accounting_account',$request->business_name);

        session_start();
        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => config('myconfig.Xero.ci'),
            'clientSecret'            => config('myconfig.Xero.sk'),
            'redirectUri'             => route('xero.redirect_uri'),
            'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
            'urlAccessToken'          => 'https://identity.xero.com/connect/token',
            'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
        ]);

        // Scope defines the data your app has permission to access.
        // Learn more about scopes at https://developer.xero.com/documentation/oauth2/scopes
        $options = [
            'scope' => ['openid email profile offline_access accounting.settings accounting.transactions accounting.contacts accounting.journals.read accounting.reports.read accounting.attachments']
        ];

        // This returns the authorizeUrl with necessary parameters applied (e.g. state).
        $authorizationUrl = $provider->getAuthorizationUrl($options);

        // Save the state generated for you and store it to the session.
        // For security, on callback we compare the saved state with the one returned to ensure they match.


        // Redirect the user to the authorization URL.
        header('Location: ' . $authorizationUrl);


    }
    public function callback(Request $request)
    {

ini_set('display_errors', 'On');


// Storage Classe uses sessions for storing token > extend to your DB of choice


$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => config('myconfig.Xero.ci'),
    'clientSecret'            => config('myconfig.Xero.sk'),
    'redirectUri'             => route('xero.redirect_uri'),
    'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
    'urlAccessToken'          => 'https://identity.xero.com/connect/token',
    'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {
    echo "Something went wrong, no authorization code found";
    exit("Something went wrong, no authorization code found");

    // Check given state against previously stored one to mitigate CSRF attack
} else {

    try {
        // Try to get an access token using the authorization code grant.
        $accessTokenResponce = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
        $accessToken=$accessTokenResponce->getToken();
        $data=decode_access_token($accessToken);
       $refresh_token=$accessTokenResponce->getRefreshToken();
        $url='https://api.xero.com/connections';

        $res=Http::withToken($accessToken)->get($url);
        $res=$res->json();
        ;

        $account_id=$res[0]['tenantId'];

        if (!AccountingConnection::where(['user_id'=>auth()->user()->id,'account_id'=>$account_id,'service_name'=>'Xero','deleted_at'=>null])->where('status','connected')->exists())
        {

            AccountingConnection::where(['user_id'=>auth()->user()->id,'business_name'=>Session::get('accounting_account')])->update([
                'user_id'=>auth()->user()->id,
                'account_id'=>$account_id,
                'service_name'=>'Xero',
                'access_token'=>$accessToken,
                'status'=>'connected',
                'refresh_token'=>$refresh_token
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


    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        dd($e);
        exit();
    }
}

    }
    public static function sendTransaction($connection_id,$vendorName,$issue_date,$amount,$due_offset_days=0,$currency_code='CAD',$language='en',$categoryid='3541633')
    {
        $b=AccountingConnection::find($connection_id);
        self::$temp_id=null;
        self::$attemps=0;
        self::createVendor($vendorName,$connection_id);
        if (self::$temp_id!=null)
        {

            $response=Http::withToken($b->access_token)->post(config('myconfig.Freshbook.url').'/accounting/account/'.$b->account_id.'/bills/bills',[
                'bill'=>[
                    'vendorid'=>self::$temp_id,
                    'issue_date'=>$issue_date,
                    'due_offset_days'=>$due_offset_days,
                    'currency_code'=>$currency_code,
                    'language'=>$language,
                    'lines'=>[
                        [
                            'unit_cost'=>[
                                'amount'=>$amount,
                                'code'=>$currency_code
                            ],
                            'quantity'=>1,
                            'categoryid'=>$categoryid
                        ]
                    ]
                ]
            ]);
            $response=$response->json();

        }


    }
    public static function createVendor($display_name,$connection_id,$currency_code='CAD',$language='en')
    {
        $b=AccountingConnection::find($connection_id);
        $response=Http::withToken($b->access_token)->post(config('myconfig.Freshbook.url').'/accounting/account/'.$b->account_id.'/bill_vendors/bill_vendors',[
            'bill_vendor'=>[
                'vendor_name'=>$display_name,
                'currency_code'=>$currency_code,
                'language'=>$language
            ]
        ]);

        if ($response->status()==401 || $response->status()=="401")
        {
            self::$attemps=self::$attemps+1;

            if (self::$attemps<=3)
            {
                $b->status="expired";
                $b->save();

                self::refreshToken($connection_id);
                self::createVendor($display_name,$connection_id,$currency_code,$language);
            }else
            {
                self::$attemps=0;
                return false;
            }

            $b->save();
        }

        if ($response->failed())
        {

            $response=$response->json();
            $errors=$response['response']['errors'];
            if ($errors[0]['errno']=='31002')
            {
                self::getVendorByName($connection_id,$display_name);
            }

        }
        else
        {
            $response=$response->json();
            $result=$response['response']['result'];
            $vendor=$result['bill_vendor'];
            self::$temp_id=$vendor['vendorid'];
        }


//asas
    }
    public static function getVendorByName($connection_id,$name,$page=1)
    {

        $b=AccountingConnection::find($connection_id);
        $vendors=Http::withToken($b->access_token)->get(config('myconfig.Freshbook.url').'/accounting/account/'.$b->account_id.'/bill_vendors/bill_vendors?page='.$page);
        $vendors=$vendors->json();

        $result=$vendors['response']['result'];
        $list=$result['bill_vendors'];

        if ($result['total']>0)
        {
            foreach ($list as $b)
            {

                if ($b['vendor_name']==$name)
                {

                    self::$temp_id=$b['vendorid'];

                }
            }
        }

        if (self::$temp_id || $page==$result['pages'])
        {

            return self::$temp_id;
        }
        else
        {

            self::getVendorByName($connection_id,$name,$result['page']+1);
        }
    }
    public static function refreshToken($connection_id)
    {
        $b=AccountingConnection::find($connection_id);
        $response=Http::withToken($b->access_token)->post('https://api.freshbooks.com/auth/oauth/revoke',[
            'client_id'=>config('myconfig.Freshbook.ci'),
            'client_secret'=>config('myconfig.Freshbook.sk'),
            'token'=>$b->refresh_token
        ]);
        //token2
        dd($response->json());
    }
}
