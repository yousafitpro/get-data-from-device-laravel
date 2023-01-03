<?php

namespace App\Http\Controllers;

use App\Jobs\chargeTrialUserJob;
use App\Jobs\createBillPaymentOrderJob;
use App\Models\BankAccount;
use App\Models\LocBankAccount;
use App\Models\Merchant\merchantCompany;
use App\Models\Merchant\merchantOffers;
use App\Models\packageTransaction;
use App\Models\PaidBill;
use App\Models\testRequest;
use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\membershipwelcomeNotification;
use App\Notifications\oneday_before_charging_card_bill_payment;
use App\Notifications\passwordChangedNotification;
use App\Notifications\trialVersionActivatedNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use phpDocumentor\GraphViz\Exception;

class sqaureController extends Controller
{



    public function index(Request $request)
    {
        return view('sqaure.index');
    }
    public function all_transactions(Request $request)
    {
        $data['list']=packageTransaction::all();
        return view('sqaure.all-transactions',$data);
    }
  public function isValidCallback($callbackBody, $callbackSignature) {
        $webhookUrl="https://account.zpayd.com/api/square/webhook-payment";
       $webhookSignatureKey="vKjRbobgCGXiTrvhQ1ycQQ";

        # Combine your webhook notification URL and the JSON body of the incoming request into a single string
        $stringToSign = $webhookUrl . $callbackBody;

        # Generate the HMAC-SHA1 signature of the string, signed with your webhook signature key
        $stringSignature = base64_encode(hash_hmac('sha1', $stringToSign, $webhookSignatureKey, true));

        # Hash the signatures a second time (to protect against timing attacks)
        # and compare them
        return (sha1($stringSignature) === sha1($callbackSignature));
    }

    public function webhook_payment(Request $request)
    {

//        if ($request->type=='')
        $r=new testRequest();
        $data=$request->collect();
        $r->content=$data;
        $r->save();

        $data=json_decode($r->content);
        $obj=$data->data->object;

        $r->content=$obj->payment->id.$obj->payment->status;
        $r->save();

        if (merchantOffers::where('external_transaction_id',$obj->payment->id)->exists())
        {
            if ($obj->payment->status=='APPROVED')
            {
             if (merchantOffers::where('external_transaction_id',$obj->payment->id)->where('status','!=','APPROVED')->exists())
             {
                 merchantOffers::where('external_transaction_id',$obj->payment->id)->update([
                     'status'=>"APPROVED"
                 ]);
                 $o=merchantOffers::where('external_transaction_id',$obj->payment->id)->first();
                 $c=merchantCompany::where('user_id',$o->user_id)->first();
//                 $d=$o->amount/100;
//                 $commission=$d*$c->commission;
                 $amount=$o->amount;
                 add_balance_to_wallet($o->user_id,$amount);
//                 $data['offer']=merchantOffers::find($o->id);
//                 event(new \App\Events\clientSubmittedThePaymentEvent($data));
             }

                ///asdasadasdasasdasdasdasdasd
            }


        }


    }
    public function add_debit_view()
    {

         return view('sqaure.add_debit');
    }
    public function add_debit()
    {

    }
    public function add_credit_view()
    {

        return view('sqaure.add_credit');
    }
    public function add_credit()
    {

    }
    public function save_token(Request $request)
    {

         $o=merchantOffers::find($request->offer_id);
         $u=User::find($o->user_id);
         $c=merchantCompany::where('user_id',$u->id)->first();
         if ($o->status!="zPAYD-Started")
         {
             return response()->json(['code'=>1,'message'=>"Sorry request already processed"]);
         }
        $customer= Http::withToken(Config::get('myconfig.Square.token'))->post(Config::get('myconfig.Square.url').'/v2/customers',[
            'idempotency_key'=>str_random(20),
            "given_name"=> $o->name,
            "family_name"=> $o->name,
            "company_name"=> $o->name,
            "nickname"=> $o->name,
            "email_address"=>$o->email,
//                "phone_number"=> null,
            "reference_id"=> 'u'.$o->id,
            "note"=> "A New User",
            "birthday"=> "2008-03-05T00:00:00-00:00"
        ]);


        if ($customer->status()=='200')
        {
            $customer=$customer->json();

            $o->gateway_customer_id=$customer['customer']['id'];
            $o->save();
            try {


                $card= Http::withToken(Config::get('myconfig.Square.token'))->post(Config::get('myconfig.Square.url').'/v2/cards',[
                    'idempotency_key'=>$o->id.str_random(20),
                    'source_id'=>$request->token,
                    'card'=>[
                        'customer_id'=>$o->gateway_customer_id
                    ]
                ]);

                //sas
                if ($card->status()!='200')
                {
                    return response()->json(['code'=>0,'message'=>$card->json()]);
                }
                $card=$card->json();
                $d=$o->amount/100;
                $commission=$d*square_commission();
                $amount=$o->amount+$commission;
                $amount=dollar_to_cents($amount);
                $payment= Http::withToken(Config::get('myconfig.Square.token'))->post(Config::get('myconfig.Square.url').'/v2/payments',[
                    'idempotency_key'=>str_random(20),
                    'autocomplete'=>true,
                    'amount_money'=>[
                        'amount'=>$amount,
                        'currency'=>"CAD"
                    ],
                    'source_id'=>$card['card']['id'],
                    'customer_id'=>$o->gateway_customer_id
                ]);
                if ($payment->status()=='200')
                {
                    //asdasd
                    $o->external_transaction_id=$payment['payment']['id'];
                    $o->save();
                    ///asdasd
                    $data['offer']=merchantOffers::find($o->id);

                    event(new \App\Events\clientSubmittedThePaymentEvent($data));
                        return response()->json(['code'=>1,'message'=>"Transaction Successful"]);

                }
                else
                {
                    return response()->json(['code'=>0,'message'=>$payment->json()]);

                }




            }
            catch (Exception $e)
            {
                return response()->json(['code'=>0,'message'=>$e]);
            }

        }

    }
//    public function save_card_token(Request $request)
//    {
//
//
//        $req=null;
//        try {
//
//
//            $req= Http::withToken(Config::get('myconfig.Square.token'))->post(Config::get('myconfig.Square.url').'/v2/cards',[
//                'idempotency_key'=>auth()->user()->id.str_random(20),
//                'source_id'=>$request->token,
//                'card'=>[
//                    'customer_id'=>auth()->user()->sqaure_customer_id
//                ]
//            ]);
//
//            //sas
//            if ($req->status()!='200')
//            {
//                return response()->json(['code'=>0,'card'=>$req->json()]);
//            }
//            $req=$req->json();
//
//            if ($request->type=='credit')
//            {
//                $p=true;
//                if (LocBankAccount::where(['deleted_at'=>null,'user_id'=>auth()->user()->id,'is_primary'=>true])->exists())
//                {
//                    $p=false;
//                }
//                $c=LocBankAccount::create([
//                    'user_id'=>auth()->user()->id,
//                    'nic_name'=>$request->name,
//                    'title'=>$req['card']['last_4'],
//                    'bank_id'=>$req['card']['id'],
//                    'bank_name'=>$req['card']['card_brand'],
//                    'is_primary'=>$p
//                ]);
//                return response()->json(['code'=>1,'message'=>"Credit Card Successfully Added",'card'=>$c]);
//
//            }else
//            {
//
//
//
//
//                if (auth()->user()->valid_to<=Carbon::now()->toDateString() || auth()->user()->valid_to=='renew' || auth()->user()->valid_to==null)
//                {
//                    $u=User::find(auth()->user()->id);
//                    $us=UserSetting::where("user_id",auth()->user()->id)->first();
//                    if ($u->valid_till!='renew' && $us->is_membership_expired=='false')
//                    {
//                        return response()->json(['code'=>1,'message'=>"Your membership is already activated"]);
//
//                    }
//                    if ($request->name!=auth()->user()->name)
//                    {
//                        return response()->json(['code'=>1,'message'=>"Sorry! Card Holder Name and Name on zPAYD Should be Same."]);
//
//                    }
//                    $amount=trim($request->amount, "$");
//
//                    if (!is_float($amount))
//                    {
//                        $amount=floatval($amount);
//                    }
//                    if (user_setting(auth()->user()->id)->is_trial_used)
//                    {
//                        $tempData['user']=auth()->user();
//                        $tempData['card_id']=$req['card']['id'];
//                        $tempData['duration']=$request->duration;
//                        $tempData['name']=auth()->user()->name;
//                        $tempData['amount']=$amount*100;
//                        //asdasd
//                        $date=Carbon::now()->addMonth();
//                        $user=User::find(auth()->user()->id);
//                        $user->valid_till=$date->toDateString();
//                        $user->save();
//                        UserSetting::where("user_id",auth()->user()->id)->update([
//                            'is_membership_expired'=>'false',
//                            'is_trial_used'=>'true'
//                        ]);
//                        ///adasdasasassdadasassdsfsdsdfsdf
//
//                        chargeTrialUserJob::dispatch($tempData)->delay(Carbon::parse(today_date())->addDays(29));
//                        AlertController::create([
//                            'message'=>$user->name." 30 Day Free Trial allows you to evaluate our platform and all great benefits we offer to you.",
//                            'title'=>"Trial Membership Activated",
//                            'type'=>'network',
//                            'receiver'=>$user->id,
//                            'sender'=>$user->id
//                        ]);
//                        //  Notification::route('mail', $user->email)->notify(new trialVersionActivatedNotification($tempData));
//
//                        return response()->json(['code'=>1,'message'=>"Trial version successfully Activated."]);
//
//                    }
//                    $amount=dollar_to_cents($amount);
//                    $req= Http::withToken(Config::get('myconfig.Square.token'))->post(Config::get('myconfig.Square.url').'/v2/payments',[
//                        'idempotency_key'=>str_random(20),
//                        'autocomplete'=>true,
//                        'amount_money'=>[
//                            'amount'=>,
//                            'currency'=>"CAD"
//                        ],
//                        'source_id'=>$req['card']['id'],
//                        'customer_id'=>auth()->user()->sqaure_customer_id
//                    ]);
//                    if ($req->status()=='200')
//                    {
//                        packageTransaction::create([
//                            'user_id'=>auth()->user()->id,
//                            'package_id'=>auth()->user()->package_id,
//                            'amount'=>$amount,
//                            'type'=>'package',
//                            'transaction_id'=>$req['payment']['id'],
//                            'duration'=>$request->duration
//                        ]);
//                        if ($request->duration!='year')
//                        {
//                            return response()->json(['code'=>1,'message'=>"Transaction Successfully Started .This account will be charged the monthly fee of ".user_package_price()." You can end the subscription at any time by emailing us at support@zpayd.com"]);
//                        }
//                        else
//                        {
//                            return response()->json(['code'=>1,'message'=>"Transaction Successfully Started. This account will be charged the annual fee of ".user_package_price()." You can end the subscription at any time by emailing us at support@zpayd.com"]);
//                        }
//
//
//                    }
//                    else
//                    {
//                        return response()->json(['code'=>0,'message'=>$req->json()]);
//
//                    }
//
//                }
////                else
////                {
////                    $p=1;
////                    if (BankAccount::where(['deleted_at'=>null,'user_id'=>auth()->user()->id,'is_primary'=>'1'])->exists())
////                    {
////                        $p=0;
////                    }
////                    $c= BankAccount::create([
////                        'user_id'=>auth()->user()->id,
////                        'nic_name'=>$request->name,
////                        'title'=>$req['card']['last_4'],
////                        'bank_id'=>$req['card']['id'],
////                        'bank_name'=>$req['card']['card_brand'],
////                        'is_primary'=>$p
////                    ]);
////                    return response()->json(['code'=>1,'message'=>"Debit Card Successfully Added",'card'=>$c]);
////
////                }
//            }
//        }
//        catch (Exception $e)
//        {
//            return response()->json(['code'=>0,'message'=>$e]);
//        }
//
//
//
//
//
//
//    }
}
