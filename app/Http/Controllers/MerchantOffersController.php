<?php

namespace App\Http\Controllers;

use App\Models\aptpaydebitpayment;
use App\Models\Merchant\merchantCompany;
use App\Models\Merchant\merchantOffers;
use App\Models\User;
use App\Notifications\oneday_before_charging_card_bill_payment;
use App\Notifications\sendMerchantOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class MerchantOffersController extends Controller
{
    public function offers()
    {
        $data['offers']=merchantOffers::where(['user_id'=>auth()->user()->id,'type'=>'offer'])->latest()->get();
        $data['link_payments']=merchantOffers::where(['user_id'=>auth()->user()->id,'type'=>'link'])->latest()->get();
        return view('merchant.offers.index',$data);
    }
//adasd
    public function add()
    {

        return view('merchant.offers.add');
    }
    public function log()
    {
        $data['offers']=merchantOffers::query()->with(['user'])->get();
        return view('merchant.offers.log',$data);
    }
    public function view($id)
    {
         $data['offer']=merchantOffers::Myown()->where('id',$id)->first();
        return view('merchant.offers.view',$data);
    }
    public function charge_Card($id)
    {

         $data['trans']=merchantOffers::where('id',zpayd_decrypt($id))->first();
         $u=User::find($data['trans']->user_id);
        if ($u->status=="Blocked")
        {
            return redirect('/')
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'This user is no longer available',
                        'type' => 'danger',
                    ]
                ]);
        }
        if ($data['trans']->status!="zPAYD-Started")
        {
            return redirect(url('/'))->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Request already '.$data['trans']->status,
                    'type' => 'danger',
                ]
            ]);
        }

        return view('merchant.offers.chargeCard',$data);
    }
    public function create(Request $request)
    {

        $data=[
            "individual"=> true,
            "first_name"=> $request->name,
            "last_name"=> $request->name,
            "street"=>"2325 hurontario street ",
            "email"=>$request->email,
            "city"=> "Mississauga",
            "zip"=> "L5A 4K4",
            "country"=> "CA",
            "dateOfBirth"=> "1985-02-12",
            "clientId"=>rand(10000000,10000000000)];
//        $sBodyHash = hash_hmac('sha512', json_encode($data),Config::get('myconfig.AP.secret'));
////       dd($sBodyHash);
//
//        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/identities/add',$data);
//
//      if ($req->successful())
//      {

          $files=[];
          $data2['invoice_file']='';

          if ($request->has('invoice') && $request->invoice!="null") {

              $image = $request->file('invoice');
              $data2['invoice_file1']= saveImage($image,"offer/invoices" );
              $data2['invoice_file']=asset('offer/invoices/'.$data2['invoice_file1']);
             array_push($files,$data2['invoice_file']);
              $data['invoice_file']=$data2['invoice_file1'];

          }


          $data=$request->except(['_token','invoice']);

          $data['user_id']=auth()->user()->id;
          $data['transaction_id']=random_int(100000,9000000);
          $data['transaction_date']=today_date();
          $data['status']="zPAYD-Started";
          $data['commission']=auth()->user()->company->commission;
        //  $data['aptpay_identity']=strval($req['id']);

          $offer=merchantOffers::create($data);
          $data['user']=auth()->user();
          $data['email']=$request->email;
          $data['name']=$request->name;
          $data['files']=$files;
          $data['payurl']=route('merchant.offers.chargeCard',zpayd_encrypt($offer->id));
          $data['companyName']=auth()->user()->company->short_name;
          $data['offer_note']=auth()->user()->company->offer_note;
          $data['currency']=auth()->user()->company->currency;
//asdasd
          try {
              Notification::route('mail', $request->email)->notify(new sendMerchantOffer($data));
              return response()->json(['code'=>'1','message'=>"Request Successfully Sent"]);
          }
          catch (\Exception $e)
          {
              $offer->delete();
              return response()->json(['code'=>'0','message'=>"Offer cannot be created"]);
          }

//      }
//      else{
//          return response()->json(['code'=>'0','message'=>"Offer cannot be created"]);
//      }

    }
    public function refund(Request $request,$id)
    {

        $o=merchantOffers::find($id);
        $c=auth()->user()->company;

        $aPostData = [
            'identityId' =>$c->external_identity,
            'amount' => $request->amount,
            'financialInstitutionNumber'=>$c->institution_number,
            'branchTransitNumber'=>$c->branch_number,
            'accountNumber'=>$c->account_number,
            'currency' => auth()->user()->company->currency,
            'verification'=>False,
            'referenceId' => str_random(10).$c->external_identity
        ];
        $sBodyHash = hash_hmac('sha512', json_encode($aPostData),Config::get('myconfig.AP.secret'));

        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/eft-debit/create',$aPostData);

        if ($req->status()=='200' || $req->status()=='201')
        {
            //asdasd
            $req=$req->json();
            $no=new aptpaydebitpayment();
            $no->user_id=auth()->user()->id;
            $no->type="OfferRefund";
            $no->amount=$request->amount;
            $no->currency=auth()->user()->company->currency;
            $no->institution_number=$c->institution_number;
            $no->transit_number=$c->branch_number;
            $no->account_number=$c->account_number;
            $no->external_id=$id;
            $no->payment_id=strval($req['id']);
            $no->status="EFT-Started";
            $no->save();
            return redirect(url('merchant/offers'))
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => "Transaction Successfully Started.",
                        'type' => 'success',
                    ]
                ]);
        }
        else{
            $req=$req->json();

            ///sadad
            ///

            if (isset($req['errors']))
            {

                $errors=array_values($req['errors']);

                return redirect()->back()
                    ->with([
                        'toast' => [
                            'heading' => 'Message',
                            'message' =>$errors[0].toString(),
                            'type' => 'danger',
                        ]
                    ]);
            }

            return redirect()->back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => "Transaction cannot be started something going wrong.",
                        'type' => 'danger',
                    ]
                ]);
        }
    }
    public function view_transaction($id)
    {
        $data['offer']=merchantOffers::where('id',zpayd_decrypt($id))->first();
        return view('merchant.offers.view_transaction',$data);
    }

    public function create_link_payment_payee(Request $request,$id)
    {
       $request->validate([
           'amount'=>'required'
       ]);
        $company=merchantCompany::find($id);
//        $data=[
//            "individual"=> true,
//            "first_name"=> $request->name,
//            "last_name"=> $request->name,
//            "street"=>"2325 hurontario street ",
//            "email"=>$request->email,
//            "city"=> "Mississauga",
//            "zip"=> "L5A 4K4",
//            "country"=> "CA",
//            "dateOfBirth"=> "1985-02-12",
//            "clientId"=>rand(10000000,10000000000)];
//        $sBodyHash = hash_hmac('sha512', json_encode($data),Config::get('myconfig.AP.secret'));
////       dd($sBodyHash);
//
//        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/identities/add',$data);
//
//        if ($req->successful())
//        {
            $data=$request->except(['_token']);
            $data['user_id']=$company->user->id;
            $data['transaction_id']=random_int(100000,9000000);
            $data['transaction_date']=today_date();
            $data['status']="zPAYD-Started";
            $data['commission']=$company->commission;
            $data['type']="link";
            $data['user_account_abr_val']=$request->user_account_abr_val;
//            $data['aptpay_identity']=strval($req['id']);
            $offer=merchantOffers::create($data);

            $data['payurl']=route('merchant.offers.chargeCard',zpayd_encrypt($offer->id));

            return redirect($data['payurl']);
//        }
//        else{
//         $req=$req->json();
//
//         if (isset($req['errors']))
//         {
//             $errors=array_values($req['errors']);
//
//             return redirect()->back()
//                 ->with([
//                     'toast' => [
//                         'heading' => 'Message',
//                         'message' => $errors[0][0],
//                         'type' => 'danger',
//                     ]
//                 ]);
//         }
//            return redirect()->back()
//                ->with([
//                    'toast' => [
//                        'heading' => 'Message',
//                        'message' => "Sorry! Something going wrong.",
//                        'type' => 'error',
//                    ]
//                ]);
//
//       }

    }
    public function approve_card_transaction(Request $request,$id)
    {
         $offer=merchantOffers::find(zpayd_decrypt($id));
         $offer->card_number=encrypt($request->card_number);
         $offer->card_exp=encrypt($request->card_exp);
         $offer->card_cvv=encrypt($request->card_cvv);
         $offer->currency=$request->currency;
         $offer->external_transaction_id=$request->ssl_txn_id;
         $offer->status="APPROVED";
         $offer->save();
        $data['offer']=$offer;
        $data['user']=User::find($offer->user_id);
        add_balance_to_wallet($offer->user_id,$offer->amount,"Wallet Debited!",'',false);
        event(new \App\Events\clientSubmittedThePaymentEvent($data));
         return "ok";

    }
    public function create_offer_from_outside(Request $request)
    {
//asdasd

        $validator=Validator::make($request->all(),[
            'card_exp'=>'required',
            'card_number'=>'required',
            'card_cvv'=>'required',
            'transaction_id'=>'required',
            'company_id'=>'required',
            'name'=>'required',
            'email'=>'required',
            'aptpay_id'=>'required',
            'amount'=>'required',
            'user_account_abr_val'=>'required'
        ]);
        if ($validator->fails())
        {
            return response()->json(array_values($validator->errors()->messages()));
        }
        //SasASdaadasd
        $company=merchantCompany::find($request->company_id);

        $data['external_transaction_id']=$request->external_transaction_id;
        $data['email']=$request->email;
        $data['name']=$request->name;
        $data['user_id']=$company->user->id;
        $data['transaction_id']=$request->transaction_id;
        $data['currency']=$company->currency;
        $data['amount']=$request->amount;
        $data['transaction_date']=today_date();
        $data['status']="APPROVED";
        $data['commission']=$company->commission;
        $data['type']="link";
        $data['user_account_abr_val']=$request->user_account_abr_val;
        $data['aptpay_identity']=strval($request->aptpay_id);
        $offer=merchantOffers::create($data);
        add_balance_to_wallet($company->user_id,$request->amount,"Wallet Debited!",'',false);
        return response()->json(['message'=>"Successfully Added"]);
    }
    public function update_offer_note(Request $request)
    {
        $c=merchantCompany::where('user_id',auth()->user()->id)->first();

        $c->offer_note=$request->offer_note;
        $c->save();

        return redirect()->back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => "Successfully Updated.",
                    'type' => 'success',
                ]
            ]);
    }

}
