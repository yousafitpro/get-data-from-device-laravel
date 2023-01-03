<?php

namespace App\Http\Controllers;

use App\Models\AccountingConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;

class freshbookCOntroller extends Controller
{
   public static $temp_id=null;
   public static $attemps=0;
    public function connect(Request $request)
    {
        Session::put('accounting_account',$request->business_name);
        $url='https://auth.freshbooks.com/oauth/authorize?client_id='.config('myconfig.Freshbook.ci').'&response_type=code&redirect_uri=https%3A%2F%2Faccount.zpayd.com%2Ffreshbook%2Fredirect_url&scope=user%3Aprofile%3Aread%20user%3Abill_payments%3Awrite%20user%3Abills%3Aread%20user%3Abills%3Awrite%20user%3Abill_vendors%3Aread%20user%3Abill_vendors%3Awrite%20user%3Abillable_items%3Aread%20user%3Abillable_items%3Awrite%20user%3Aexpenses%3Awrite%20user%3Aexpenses%3Aread%20user%3Abill_payments%3Aread%20user%3Aclients%3Aread%20user%3Abusiness%3Aread%20user%3Aclients%3Awrite%20user%3Acredit_notes%3Aread%20user%3Acredit_notes%3Awrite%20user%3Aestimates%3Aread%20user%3Aestimates%3Awrite%20user%3Ainvoices%3Aread%20user%3Ainvoices%3Awrite%20user%3Ajournal_entries%3Aread%20user%3Ajournal_entries%3Awrite%20user%3Anotifications%3Aread%20user%3Aonline_payments%3Aread%20user%3Aonline_payments%3Awrite%20user%3Aother_income%3Aread%20user%3Aother_income%3Awrite%20user%3Apayments%3Aread%20user%3Apayments%3Awrite%20user%3Aprojects%3Aread%20user%3Aprojects%3Awrite%20user%3Areports%3Aread%20user%3Aretainers%3Aread%20user%3Aretainers%3Awrite%20user%3Ataxes%3Aread%20user%3Ataxes%3Awrite%20user%3Ateams%3Aread%20user%3Ateams%3Awrite%20user%3Atime_entries%3Aread%20user%3Atime_entries%3Awrite%20user%3Auploads%3Aread%20user%3Auploads%3Awrite';
        return redirect($url);
    }
    public function callback(Request $request)
    {

         $response=Http::post(config('myconfig.Freshbook.url').'/auth/oauth/token',[
              'code'=>$request->code,
             'grant_type'=>'authorization_code',
             'client_id'=>config('myconfig.Freshbook.ci'),
             'client_secret'=>config('myconfig.Freshbook.sk'),
             'redirect_uri'=>route('freshbook.redirect_uri')
         ]);
         $response=$response->json();


        $me=Http::withToken($response['access_token'])->get(config('myconfig.Freshbook.url').'/auth/api/v1/users/me');
        $me=$me->json();
        $role=$me['response']['roles'];

         if (!AccountingConnection::where(['user_id'=>auth()->user()->id,'account_id'=>$role[0]['accountid'],'service_name'=>'Freshbooks','deleted_at'=>null])->where('status','connected')->exists())
         {
             AccountingConnection::where(['user_id'=>auth()->user()->id,'business_name'=>Session::get('accounting_account')])->update([
                 'user_id'=>auth()->user()->id,
                 'account_id'=>$role[0]['accountid'],
                 'service_name'=>'Freshbooks',
                 'access_token'=>$response['access_token'],
                 'status'=>'connected',
                 'refresh_token'=>$response['refresh_token']
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
            $result=$response['response']['result'];
            $bill=$result['bill'];
            $response2=Http::withToken($b->access_token)->post(config('myconfig.Freshbook.url').'/accounting/account/'.$b->account_id.'/bill_payments/bill_payments',[
                'bill_payment'=>[
                    'billid'=>$bill['id'],
                    'amount'=>[
                        'amount'=>$amount,
                        'code'=>$currency_code
                    ],
                    'payment_type'=>'check',
                    'paid_date'=>$issue_date,
                    ]
            ]);

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
        $response=Http::post(config('myconfig.Freshbook.url').'/auth/oauth/token',[
            'refresh_token'=>$b->refresh_token,
            'grant_type'=>'refresh_token',
            'client_id'=>config('myconfig.Freshbook.ci'),
            'client_secret'=>config('myconfig.Freshbook.sk'),
            'redirect_uri'=>route('freshbook.redirect_uri')
        ]);
        if ($response->ok())
        {
            $response=$response->json();

            $b->status="connected";
            $b->access_token=$response['access_token'];
            $b->refresh_token=$response['refresh_token'];
            $b->save();
        }


    }
    public static function checkTokenIsValid($connection_id,$count=0)
    {
        $b=AccountingConnection::find($connection_id);
        $url=config('myconfig.Freshbook.url').'/auth/api/v1/users/me';
        $response=Http::withToken($b->access_token)->get($url);
        if ($response->status()==401)
        {
            $b->status='expired';
            $b->save();

            self::refreshToken($connection_id);
        }

    }
}
