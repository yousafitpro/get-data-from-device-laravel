<?php

namespace App\Http\Controllers;

use App\Models\efttransaction;
use App\Models\LocBankAccount;
use App\Models\packageTransaction;
use App\Models\sendandrequestfund;
use App\Models\User;
use App\Notifications\billChargedFromCardInsteadWallet;
use App\Notifications\billPaymentStartedUsingWallet;
use App\Notifications\fundingwallet;
use App\Notifications\oneday_before_charging_card_bill_payment;
use App\Notifications\requestFund;
use App\Notifications\sendFund;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

class fundController extends Controller
{
    public function send(Request $request)
    {
        if (my_wallet_balance(auth()->id())<$request->amount)
        {
            return response()->json(['message'=>"sorry! insufficient balance"]);
        }
        $tempData['has_account']=true;
        $tempData['user']=auth()->user();
        $tempData['amount']=$request->amount;
        if (!User::where('email',$request->email)->exists())
        {
            $ouser_id=0;
            $ouser_username=$request->email;
            $tempData['has_account']=false;

        }else
        {
            $osuer=User::where('email',$request->email)->first();
            $ouser_id=$osuer->id;
            $ouser_username=null;
            $tempData['has_account']=true;
            AlertController::create([
                'message'=>$tempData['user']->name." is sending you funds. Please check your mailbox.",
                'title'=>"Getting Funds!",
                'type'=>'network',
                'receiver'=>$osuer->id,
                'sender'=>auth()->user()->id
            ]);
        }



        $item=sendandrequestfund::create([
            'user_id'=>$tempData['user']->id,
            'receiver_id'=>$ouser_id,
            'receiver_username'=>$ouser_username,
            'status'=>"Started",
            'amount'=>$request->amount,
            'direction'=>'send'
        ]);
        $tempData['item']=$item;
        Notification::route('mail',$request->email)->notify(new sendFund($tempData));
        return response()->json(['message'=>"Request successfully sent"]);

    }
    public function request(Request $request)
    {
        $tempData['has_account']=true;
        $tempData['user']=auth()->user();
        $tempData['amount']=$request->amount;
        if (!User::where('email',$request->email)->exists())
        {
            $ouser_id=0;
            $ouser_username=$request->email;
            $tempData['has_account']=false;
        }else
        {
            $osuer=User::where('email',$request->email)->first();
            $ouser_id=$osuer->id;
            $ouser_username=null;
            $tempData['has_account']=true;
            AlertController::create([
                'message'=>$tempData['user']->name." is requesting funds. Please check your email for more details.",
                'title'=>"Request For Funds",
                'type'=>'fund',
                'receiver'=>$osuer->id,
                'sender'=>auth()->user()->id
            ]);
        }



       $item= sendandrequestfund::create([
            'user_id'=>$tempData['user']->id,
           'receiver_id'=>$ouser_id,
           'receiver_username'=>$ouser_username,
            'status'=>"Started",
            'amount'=>$request->amount,
            'direction'=>'request'
        ]);
        $tempData['item']=$item;

        Notification::route('mail',$request->email)->notify(new requestFund($tempData));
        return response()->json(['message'=>"Request successfully sent"]);
    }
    public function accept_sendrequest(Request $request,$id)
    {
        $this->is_request_expired($id);
        $obj=sendandrequestfund::where('id',$id)->where('receiver_id',auth()->user()->id)->first();

        if ($obj->status=='Started')
        {
            $sender=User::find($obj->user_id);
            $receiver=User::find($obj->receiver_id);

            if (my_wallet_balance($sender->id)>=$obj->amount)
            {

                add_balance_to_wallet($receiver->id,$obj->amount);
                sub_balance_from_wallet($sender->id,$obj->amount);
                AlertController::create([
                    'message'=>'Hi '.$sender->name." your request has been Accepted by ".$receiver->name." and your wallet has been debited for ".$obj->amount.'$.',
                    'title'=>"Fund Sent to ".$receiver->name,
                    'type'=>'fund',
                    'receiver'=>$sender->id,
                    'sender'=>auth()->user()->id
                ]);
                AlertController::create([
                    'message'=>'Hi '.$receiver->name." you have received  ".$obj->amount.'$.'.' from '.$sender->name,
                    'title'=>"Fund Received from ".$sender->name,
                    'type'=>'fund',
                    'receiver'=>$receiver->id,
                    'sender'=>auth()->user()->id
                ]);
                $obj->status="Completed";
                $obj->save();
                return redirect(url('dashboard'))
                    ->with([
                        'toast' => [
                            'heading' => 'Message',
                            'message' => 'Request successfully Accepted and funds have been received.',
                            'type' => 'success',
                        ]
                    ]);
            }
            else
            {
                return redirect(url('dashboard'))
                    ->with([
                        'toast' => [
                            'heading' => 'Message',
                            'message' => 'Sender do not have sufficient balance',
                            'type' => 'error',
                        ]
                    ]);
            }



        }
    }
    public function accept_request(Request $request,$id)
    {
        $this->is_request_expired($id);
        $obj=sendandrequestfund::where('id',$id)->where('receiver_id',auth()->user()->id)->first();

        if ($obj->status=='Started')
        {
            $sender=User::find($obj->user_id);
            $receiver=User::find($obj->receiver_id);

         if (my_wallet_balance($receiver->id)>=$obj->amount)
         {

             add_balance_to_wallet($sender->id,$obj->amount);
             sub_balance_from_wallet($receiver->id,$obj->amount);
             AlertController::create([
                 'message'=>'Hi '.$sender->name." your request confirmed and you have received ".$obj->amount.'$.',
                 'title'=>"Fund Received",
                 'type'=>'fund',
                 'receiver'=>$sender->id,
                 'sender'=>auth()->user()->id
             ]);
             AlertController::create([
                 'message'=>'Hi '.$receiver->name." your wallet has been debited for ".$obj->amount.'$.',
                 'title'=>"Fund Sent to ".$sender->name,
                 'type'=>'fund',
                 'receiver'=>$receiver->id,
                 'sender'=>auth()->user()->id
             ]);
             $obj->status="Completed";
             $obj->save();
             return redirect(url('dashboard'))
                 ->with([
                     'toast' => [
                         'heading' => 'Message',
                         'message' => 'Request successfully Accepted and funds have been sent.',
                         'type' => 'success',
                     ]
                 ]);
         }
         else
         {
             return redirect(url('dashboard'))
                 ->with([
                     'toast' => [
                         'heading' => 'Message',
                         'message' => 'You do not have sufficient balance',
                         'type' => 'error',
                     ]
                 ]);
         }



        }
        else
        {
            return redirect(url('dashboard'))
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'Request already processed.',
                        'type' => 'error',
                    ]
                ]);
        }
    }
    public function deny_sendrequest(Request $request,$id)
    {
        $this->is_request_expired($id);
        $obj=sendandrequestfund::where('id',$id)->where('receiver_id',auth()->user()->id)->first();
        if ($obj->status=='Started')
        {
            $obj->status="Denied";
            $obj->save();
            $sender=User::find($obj->user_id);
            $receiver=User::find($obj->receiver_id);
            AlertController::create([
                'message'=> 'Your request for '.$obj->amount."$ has been denied by ".$receiver->name,
                'title'=>"Fund request denied",
                'type'=>'fund',
                'receiver'=>$sender->id,
                'sender'=>auth()->user()->id
            ]);
            return redirect(url('dashboard'))
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'Request successfully Denied.',
                        'type' => 'error',
                    ]
                ]);
        }
    }
    public function deny_request(Request $request,$id)
    {
        $this->is_request_expired($id);
        $obj=sendandrequestfund::where('id',$id)->where('receiver_id',auth()->user()->id)->first();
         if ($obj->status=='Started')
         {
             $obj->status="Denied";
             $obj->save();
             $sender=User::find($obj->user_id);
             $receiver=User::find($obj->receiver_id);
             AlertController::create([
                 'message'=>' '.$receiver->name." denied your request for ".$obj->amount.'$.',
                 'title'=>"Fund request denied",
                 'type'=>'fund',
                 'receiver'=>$sender->id,
                 'sender'=>auth()->user()->id
             ]);
             return redirect(url('dashboard'))
                 ->with([
                     'toast' => [
                         'heading' => 'Message',
                         'message' => 'Request successfully Denied.',
                         'type' => 'error',
                     ]
                 ]);
         }

    }
    public function is_request_expired($id)
    {

        $req=sendandrequestfund::find($id);
        $timenow=Carbon::parse(time_now());
        $diff=$timenow->floatDiffInHours($req->created_at);
        if ($diff>24)
        {
            $req->status='Expired';
            $req->save();
        }
    }
    public function add_funds_to_wallet_using_card_view()
    {
        return view('addfunds.addfromcards');
    }
    public function add_funds_to_wallet_using_eft_view()
    {
        return view('addfunds.addfromeft');
    }
    public function add_funds_to_wallet_using_card(Request $request)
    {
        $user=auth()->user();

            $credit_card = LocBankAccount::where(['user_id' => $user->id, 'id' => $request->card_id])->first();
            $actualAmount=$request->amount;
            $amount=$request->amount+($request->amount/100)*square_commission();




            if (!is_float($amount)) {
                $amount = floatval($amount);
            }

            $amount = $amount * 100;

            if ($credit_card) {


                $req = Http::withToken(ENVController::$access_token)->post(ENVController::$sqaure_url . '/v2/payments', [
                    'idempotency_key' => str_random(20),
                    'autocomplete' => true,
                    'amount_money' => [
                        'amount' => $amount,
                        'currency' => "CAD"
                    ],
                    'source_id' => $credit_card->bank_id,
                    'customer_id' => $user->sqaure_customer_id
                ]);
//asassasasa

                if ($req->status() == 200) {



                    $re4= packageTransaction::create([
                        'user_id' => $user->id,
                        'package_id' => 0,
                        'actual_amount'=>$request->amount,
                        'amount' => $amount,
                        'receiver_id'=>$user->id,
                        'type' => 'addFundsToWallet',
                        'transaction_id' => $req['payment']['id'],
                        'duration' => ''
                    ]);

                    $ndata['user']=$user;
                    $ndata['amount']=$actualAmount;
                    $user=auth()->user();
                    $tempData['user']=$user;
                    Notification::route('mail', $user->email)->notify(new fundingwallet($tempData));

                    if(Request::capture()->expectsJson())
                    {
                        return response()->json(['message'=>'Request is being processed soon you will get email or sms confirmation.']);
                    }
                    return redirect('/dashboard')
                        ->with([
                            'toast' => [
                                'heading' => 'Message',
                                'message' =>"Request is being processed soon you will get email or sms confirmation.",
                                'type' => 'success',
                            ]
                        ]);

                }
                else
                {
                    $res=$req->json();
                    if(Request::capture()->expectsJson())
                    {
                        return response()->json(['message'=>$res['errors'][0]['detail']]);
                    }
                    return redirect()->back()
                        ->with([
                            'toast' => [
                                'heading' => 'Message',
                                'message' => $res['errors'][0]['detail'],
                                'type' => 'error',
                            ]
                        ]);
                }

            }


    }
    public function add_funds_to_wallet_using_eft(Request $request)
    {

        $request->validate([
            'account_number'=>'min:6',
            'institution_number'=>'digits:3',
            'transit_number'=>'digits:5'
        ]);
        $amount=$request->amount+eft_commission();
      $ref_ob= efttransaction::create([
            'amount'=>$request->amount+eft_commission(),
            'actual_amount'=>$request->amount,
            'account_number'=>$request->account_number,
            'institution_number'=>$request->institution_number,
            'transit_number'=>$request->transit_number,
            'user_id'=>auth()->id(),
            'sender_id'=>auth()->id(),
            'commission'=>eft_commission(),
            'receiver_id'=>auth()->id(),
            'direction'=>'debit',
        ]);
        $res=aptPayController::eft_debit_create_transaction(auth()->user()->aptpay_identity,$amount,$request->account_number,$request->institution_number,$request->transit_number,$ref_ob);

        if ($res=="D002")
        {
            return redirect('/dashboard')
                ->with([
                    'toast' => [
                        'heading' => 'Error',
                        'message' =>"Not enough balance.",
                        'type' => 'error',
                    ]
                ]);
        }
        else if($res=="D002")
        {

            return redirect('/dashboard')
                ->with([
                    'toast' => [
                        'heading' => 'Error',
                        'message' =>"Not enough balance.",
                        'type' => 'error',
                    ]
                ]);
        }
        else if($res!="true")
        {

            return redirect('/dashboard')
                ->with([
                    'toast' => [
                        'heading' => 'Error',
                        'message' =>"Transaction Cannot be Created due to some reasons.",
                        'type' => 'error',
                    ]
                ]);
        }


        $user=auth()->user();
        $tempData['user']=$user;
        AlertController::create([
            'message'=>"We have received your request to fund your zPAYD wallet. Account will be updated as soon as the payment is processed.",
            'title'=>"Funding zPAYD Account with ".$request->amount."$",
            'type'=>'addFund',
            'receiver'=>auth()->user()->id,
            'sender'=>auth()->user()->id
        ]);
        Notification::route('mail', $user->email)->notify(new fundingwallet($tempData));

        if(Request::capture()->expectsJson())
        {
            return response()->json(['message'=>'Request is being processed soon you will get email or sms confirmation.']);
        }


        return redirect('/dashboard')
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' =>"Request is being processed soon you will get email or sms confirmation.",
                    'type' => 'success',
                ]
            ]);
    }
    public function history()
    {
        //asdsad
        $data=sendandrequestfund::where(['user_id'=>auth()->id()])->with('receiver')->get();
        return response()->json($data);

    }
}
