<?php

namespace App\Http\Controllers;

use App\Models\railzBusiness;
use App\Models\testRequest;
use App\Models\User;
use App\Notifications\newBillAdded;
use App\Notifications\railzaiLinkedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use QuickBooksOnline\API\DataService\DataService;
//require 'vendor/autoload.php';
class railzController extends Controller
{

    public static function sendTransaction($user)
    {
        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => "ABw1OFFOaXNGmKCjc0z4XYiP1bBV7G3Sj2t9aTz0Tf3oPOxVAu",
            'ClientSecret' => "n9K1nVliq26oYo0S8z78C6Z8Ev6k24azcHrqw7pJ",
            'RedirectURI' => "https://account.zpayd.com/quickbook/redirect_url",
            'scope' => "com.intuit.quickbooks.accounting",
            'baseUrl' => "Development/Production"
        ));
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
//        $authorizationCodeUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
//       dd($authorizationCodeUrl);

        $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken("AB11654278860jrctizhhwDIG3UJSoFeuXsBWA30byoGJq7cmZ", "9130352999865296");
        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();
        // token = eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..URbyX-a_j8Rk8JX8ixPcVw.chTm3VjQpojlZCtBqEpntEjyvnwc0RAcUfXMygUSv-MEi_GDJl6Fo1Wjd_pPFpVjIQ5-RYqIw6Wn8J3fbXqwa3rVMxE_XEAz060NKunQfFXtuWhFr0ABWJP4K0FVoGyjxgw5Z0ILcFm3vksxTMomTs9cjlMfmOGhoV_quuO8-UvDS4EWVH0PW3u9I0QyeCIhVUuua1r1HZhdVmANN_ZLr88BIv78tgvzfRZmjsyJjG8Ew-T40TaL_0p6fdOtgAuNOu_DE1VJCsB9hm7MjMAL6zxhly5CTg5Ok2CmiUeCMLgm4OQ4qiJGb3q6Kcesr3G-sBBJxA7g42yzPrCc0FXyti2LtLZqm9BXEnEVLcexkvLPOaLO7J-5Wds8TnbkHSOiCXj21YISRs6x6-RK6sVSO19Xs1uG-KaTxWUoc5wWEqJYUJUoB2V2pRqww2QSmSf6IPP-gUp339NfgTLpyH8l3RcmvbT0C_n-NtkD9je7qddONhdvOiq1ROCmxC8E8OXJ4Djemok3qX_i3CD5WFwQvZ3ntv3tU6gy3wh52jzg9Fi_K8a1lkXnMD2fmLTnZ3cDAO5itJi3bx0Mh7t6hMRxWmOCUN2YHQvzJbVZP3ZkkWJJcZZS1bHa5zv_nHsWrJrX_MiCfoKKnBAyYOv3CRdtNLYf2DAUF1uy6iJenTKj6FethqXMFaxy9c8zaOwXKKOYqlasGimKqNoTBuOLKmvEcYuSXnoACkZK2rRnUcbHdK0.-fU0P7-9R6KepzYkZ7SJrg
        dd($accessTokenValue);

        self::getToken($user);
        $user=User::find($user->id);
        $bs=railzBusiness::where('user_id',$user->id)->where('service_status','active')->get();
        foreach ( $bs as $b)
        {



            $response=Http::withToken($user->railz_ai_access_token)
                ->post(config('myconfig.RLZ.url').'/bills/payments',[
                    'connection'=>[
                        'businessName'=>$b->name,
                        'serviceName'=>$b->serviceName
                    ],
                    'data'=>[
                        'accountRef'=>'51',
                        'billRef'=>'we12',
                        'totalAmount'=>2.00,
                        'date'=>'2021-02-02'
                    ]
                ]);
//            $response=Http::withToken($user->railz_ai_access_token)
//                ->get(config('myconfig.RLZ.url').'/bills/payments?businessName=u78878&serviceName=quickbooks');
//
            dd($response->json());

        }
    }
    public static function sendTransaction2(Request $request)
    {

    }
    public function connectView(Request $request)
    {

        self::getToken(auth()->user());

        $new['data']=railzBusiness::where(['deleted_at'=>null,'user_id'=>auth()->user()->id])->get();
//        foreach ($new['data'] as $d)
//        {
//            $d['service']=null;
//            $response=Http::withToken(auth()->user()->railz_ai_access_token)
//                ->get(config('myconfig.RLZ.url').'/connections',[
//                    'businessName'=>$d->name
//                ]);
//            if ($response->status()=='200')
//            {
//                $data['data']=$response->json();
//             foreach ($data['data']['data'] as $s)
//             {
//                 if ($s['status']=='active')
//                 {
//                     $d['service']=$s['serviceName'];
//                 }
//             }
//            }
//            else
//            {
//
//                $data['data']=[];
//
//            }
//        }

        return view('dashboard.railzView',$new);





    }
    public function Disconnect(Request $request,$id)
    {
        self::getToken(auth()->user());
        $response=Http::withToken(auth()->user()->railz_ai_access_token)
            ->put(config('myconfig.RLZ.url').'/connections/disconnect',[
                'connectionId'=>$id
            ]);
//        if ($response->ok())
//        {
//            return redirect()->back()
//                ->with([
//                    'toast' => [
//                        'heading' => 'Message',
//                        'message' => "Connection Successfully Disconnected",
//                        'type' => 'success',
//                    ]
//                ]);
//        }
        return redirect()->back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => "Connection Successfully Disconnected",
                    'type' => 'success',
                ]
            ]);

    }

    public static function getToken($user)
    {



        $response=Http::withBasicAuth(config('myconfig.RLZ.if'),config('myconfig.RLZ.key'))->get(config('myconfig.RLZ.auth_url').'/getAccess');

        if ($response->ok())
        {
            $response=$response->json();
            $user=User::find($user->id);
            $user->railz_ai_access_token=$response['access_token'];

            $user->save();
        }



    }
    public static function createBusiness($business)
    {

    }
    public static function connect(Request $request)
    {

        self::getToken(auth()->user());
        $response=Http::withToken(auth()->user()->railz_ai_access_token)
            ->post(config('myconfig.RLZ.url').'/businesses',['businessName'=>$request->business_name]);

        if ($response->status()=='409')
        {
            $response=$response->json();

            return redirect()->back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' =>   $response['error']['message'][0],
                        'type' => 'error',
                    ]
                ]);
        }
        if ($response->status()=="201")
        {
            $rb=new railzBusiness();
            $rb->name=$request->business_name;
            $rb->user_id=auth()->user()->id;
            $rb->save();
            $tempData['user']=auth()->user();

            return redirect()->back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => "Business Successfully Connected",
                        'type' => 'success',
                    ]
                ]);
        }

        return redirect()->back();

    }
    public static function updateConnect(Request $request)
    {
        self::getToken(auth()->user());
        $response=Http::withToken(auth()->user()->railz_ai_access_token)
            ->put(config('myconfig.RLZ.url').'/businesses',['businessName'=>$request->oldBusinessName,'newBusinessName'=>$request->newBusinessName]);

        if ($response->status()=='409')
        {
            $response=$response->json();

            return redirect()->back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' =>   $response['error']['message'][0],
                        'type' => 'error',
                    ]
                ]);
        }
        if ($response->status()=="200")
        {
            $rb=railzBusiness::where('name',$request->oldBusinessName)->first();
            $rb->name=$request->newBusinessName;
            $rb->user_id=auth()->user()->id;
            $rb->save();

            return redirect()->back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => "Business Successfully Updated",
                        'type' => 'success',
                    ]
                ]);
        }

        return redirect()->back();

    }
    public function addService($bname)
    {
        $data['bname']=$bname;
        $response=Http::withToken(auth()->user()->railz_ai_access_token)
            ->get(config('myconfig.RLZ.url').'/connections',[
                'businessName'=>$bname
            ]);
        if ($response->status()=='200')
        {
            $data['data']=$response->json();

        }
        else
        {

            $data['data']=[];

        }
//dd($data);
        return view('dashboard.railzService',$data);
    }
    public function disconnect_connection_web_hook(Request $request)
    {
        $r=new testRequest();
        $data=$request->collect();
        $r->content=$data;
        $r->save();

        $data=json_decode($r->content);

        if ($data->data->event=="connectionStatus" && railzBusiness::where('name',$data->data->businessName)->exists())
        {
            $obj=railzBusiness::where('name',$data->data->businessName)->first();
            $obj->serviceName=$data->data->serviceName;
            $obj->connectionId=$data->data->connectionId;
            $obj->service_status=$data->data->newStatus;
            $obj->save();
            $r->delete();
        }
    }
    public function new_connection_web_hook(Request $request)
    {
        $r=new testRequest();
        $data=$request->collect();
        $r->content=$data;
        $r->save();

        $data=json_decode($r->content);

        if (railzBusiness::where('name',$data->data->businessName)->exists() && $data->data->event=="auth")
        {
            $obj=railzBusiness::where('name',$data->data->businessName)->first();
            $obj->serviceName=$data->data->serviceName;
            $obj->connectionId=$data->data->connectionId;
            $obj->service_status="active";
            $obj->save();
            $tempData['user']=User::where('id',$obj->user_id)->first();
            Notification::send(User::where('id',$obj->user_id)->get(), new railzaiLinkedNotification($tempData));
            $r->delete();
        }
        //asas

    }
    public function push_status_web_hook(Request $request)
    {
        $r=new testRequest();
        $data=$request->collect();
        $r->content=$data;
        $r->save();

        $data=json_decode($r->content);

        if ($data->data->event=="push" && railzBusiness::where('name',$data->data->businessName)->exists())
        {
            $r=new testRequest();
            $r->content="ok12";
            $r->save();
        }
        //asas
    }
}
