<?php

namespace App\Http\Controllers;

use App\Models\aptpaydebitpayment;
use App\Models\aptpaysendpayment;
use App\Models\Merchant\merchantOffers;
use App\Models\testRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class aptPayController extends Controller
{
    public static $Countries=['AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AN', 'AO', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AW', 'AX', 'AZ', 'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BN', 'BO', 'BR', 'BS', 'BT', 'BV', 'BW', 'BY', 'BZ', 'CA', 'CC', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN', 'CO', 'CR', 'CU', 'CV', 'CX', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER', 'ES', 'ET', 'FI', 'FJ', 'FK', 'FM', 'FO', 'FR', 'GA', 'GB', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GL', 'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HM', 'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IM', 'IN', 'IO', 'IQ', 'IR', 'IS', 'IT', 'JE', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM', 'KN', 'KP', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC', 'LI', 'LK', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MF', 'MG', 'MH', 'MK', 'ML', 'MM', 'MN', 'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA', 'NC', 'NE', 'NF', 'NG', 'NI', 'NL', 'NO', 'NP', 'NR', 'NU', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PL', 'PM', 'PN', 'PR', 'PS', 'PT', 'PW', 'PY', 'QA', 'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SB', 'SC', 'SD', 'SE', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'SR', 'SS', 'ST', 'SV', 'SY', 'SZ', 'TC', 'TD', 'TF', 'TG', 'TH', 'TJ', 'TK', 'TL','TM', 'TN', 'TO', 'TR', 'TT', 'TV', 'TW', 'TZ', 'UA', 'UG', 'UM', 'US', 'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VI', 'VN', 'VU', 'WF', 'WS', 'XK', 'YE', 'YT', 'ZA', 'ZM', 'ZW'];
    public static function eft_debit_create_transaction($identityId,$amount,$bank_account,$institution_number,$transit_number,$ref_ob,$currency="CAD")
   {

       $data['amount']=$amount;
       $data['identityId']=$identityId;
       $data['currency']=$currency;
       $data['verification']=False;
       $data['referenceId']=$ref_ob->id;
       $data['financialInstitutionNumber']=$institution_number;
       $data['branchTransitNumber']=$transit_number;
       $data['accountNumber']=$bank_account;

       $sBodyHash = hash_hmac('sha512', json_encode($data),Config::get('myconfig.AP.secret'));
//       dd($sBodyHash);

       $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/eft-debit/create',$data);
dd($req->json());
       if ($req->status()=="200")
      {
       return "true";
      }else
      {

          $req=$req->json();

          return $req['error_code'];
      }


   }
    public function create_identity_for_refund(Request $request)
    {
        $data['offer']=merchantOffers::find($request->offer_id);

        return view('merchant.offers.createIdentityForRefund',$data);
    }
    public function create_offer_refund(Request $request)
    {
        $data['offer']=merchantOffers::find($request->offer_id);

        return view('merchant.offers.createOfferRefund',$data);
    }
    public function create_offer_refund_now(Request $request)
    {
        $offer=merchantOffers::find($request->offer_id);
        $aPostData = [
            'identityId' =>$offer->aptpay_identity,
            'amount' => $request->amount,
            'currency' => 'CAD',
            'transactionType'=>'INTERAC',
            'referenceId' => str_random(10).$offer->id
        ];
        $sBodyHash = hash_hmac('sha512',json_encode($aPostData),Config::get('myconfig.AP.secret'));
        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/disbursements/add',$aPostData);


        if ($req->status()=="200" || $req->status()=="201") {
            $sp=new aptpaysendpayment();
            $sp->s_type="Refund-Deposit";
            $sp->user_id=$offer->user_id;
            $sp->type="INTERAC";
            $sp->amount=$request->amount;
            $sp->email=$offer->email;
            $sp->name=$offer->name;
            $sp->transaction_id=rand(1313123,213123213123);
            $sp->status="zPAYD-Started";
            $sp->save();
        }
    }
    public function create_identity_for_refund_now(Request $request)
    {

        $minDate=Carbon::parse(today_date())->subYears(18);
        $minDate=$minDate->format('Y-m-d');
        $request->validate([
            'date_of_birth'=>'before:'.$minDate
        ]);
        if ($request->country=="US")
        {
            $request->validate([
                'state'=>'required'
            ]);
        }
        $data=[
            "individual"=> true,
            "first_name"=> $request->first_name,
            "last_name"=> $request->last_name,
            "street"=>$request->street,
            "email"=>$request->email,
            "city"=> $request->city,
            "zip"=> $request->zip,
            "phone"=> $request->phone,
            "country"=> $request->country,
            "dateOfBirth"=> $request->date_of_birth,
            "clientId"=>rand(10000000,10000000000)];
        if ($request->country=="US")
        {
            $data['province']=$request->state;
        }

        $sBodyHash = hash_hmac('sha512', json_encode($data),Config::get('myconfig.AP.secret'));
//       dd($sBodyHash);

        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/identities/add',$data);
        if ($req->status()=="200" || $req->status()=="201")
        {
            //asdas
            $req=$req->json();
            $offer=merchantOffers::find($request->offer_id);
            $offer->aptpay_identity=strval($req['id']);
            $offer->save();
            return redirect(url('merchant/offers').'?offer_id='.$request->offer_id)->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => "Identity Successfully created",
                    'type' => 'success',
                ]
            ]);

        }
        else
        {
            $req=$req->json();
            if(isset($req['errors']))
            {
                $errors=array_values($req['errors']);

                return redirect()->back()->withInput()->withErrors($errors);
            }

            return redirect()->back()->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => "Unable to create payee there is something going wrong.",
                    'type' => 'error',
                ]
            ]);
        }
    }

    public static function add_identity($data,$user)
   {


       $sBodyHash = hash_hmac('sha512', json_encode($data),Config::get('myconfig.AP.secret'));
//       dd($sBodyHash);

       $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/identities/add',$data);


    var_dump($req->json());
       if ($req->status()=="200")
       {
           $json=$req->json();
           $user->aptpay_identity=$json['id'];
           $user->save();
       }


   }
    public static function refunds(Request $request)
   {
       $data['list']=aptpaydebitpayment::Myown()->where('type','OfferRefund')->get();
       foreach ($data['list'] as $i)
       {
           $i->offer=merchantOffers::find($i->external_id);
       }
       return view('aptpay.refunds',$data);
   }
    public static function withdraws(Request $request)
    {
        $data['list']=aptpaysendpayment::where('s_type','Company-Deposit')->where('user_id',auth()->user()->id)->get();

        return view('aptpay.withdraws',$data);
    }
    public static function request_pay(Request $request)
    {
        $data=[
            'identityId'=>693048341456,
            'firstName'=>$request->first_name,
            'lastName'=>$request->last_name,
            'amount'=>10,
            'email'=>$request->email,
        ];
//        if (!User::where("email",$request->email)->exists())
//        {
//            if (Request::capture()->expectsJson())
//            {
//                return response()->json(['code'=>'0','message'=>"This Email address don't have any account on zPAYD."]);
//
//            }
//            else
//            {
//                return redirect()->back()
//                    ->with([
//                        'toast' => [
//                            'heading' => 'Message',
//                            'message' => "This Email address don't have any account on zPAYD.",
//                            'type' => 'error',
//                        ]
//                    ]);
//            }
//        }
        $sBodyHash = hash_hmac('sha512', json_encode($data),Config::get('myconfig.AP.secret'));
//       dd($sBodyHash);

        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/request-pay/create',$data);
        dd($req->json());
    }
    public function request_pay_view()
    {
        return view('aptpay.requestPayment');
    }
    public static function registerOnLogin($user)
    {

        if ($user->aptpay_identity==null)
        {
            if ($user->package_id=="1")
            {
                $data=[
                    "individual"=> true,
                    "first_name"=> $user->name,
                    "last_name"=> $user->name,
                    "street"=>$user->address,
                    "email"=>$user->email,
                    "city"=> $user->city,
                    "zip"=> $user->zipcode,
                    "country"=> $user->country,
                    "dateOfBirth"=> $user->dateOfBirth,
                    "clientId"=>$user->id];
                \App\Http\Controllers\aptPayController::add_identity($data,$user);
            }
            if ($user->package_id=="2")
            {


                $data=[
                    "individual"=> false,
                    "name"=> $user->name,
                    "street"=> $user->address,
                    "dateOfBirth"=> $user->dateOfBirth,
                    "dbaName"=> $user->	dbaName,
                    "url"=> $user->url,
                    "typeOfBusiness"=> $user->typeOfBusiness,
                    "dateOfIncorporation"=> $user->dateOfIncorporation,
                    "province"=> $user->province,
                    "countryOfRegistration"=> $user->countryOfRegistration,
                    "provinceOfRegistration"=> $user->provinceOfRegistration,
                    "businessTaxId"=> $user->businessTaxId,
                    "email"=>$user->email,
                    "city"=> $user->city,
                    "zip"=> $user->zipcode,
                    "country"=> $user->country,
                    "clientId"=>$user->id];
                \App\Http\Controllers\aptPayController::add_identity($data,$user);
            }
        }
    }
    public static function registerWebhook()
    {
        $data=[
//            'url'=>"https://account.zpayd.com/api/aptpay/webhook",
        ];
        $sBodyHash = hash_hmac('sha512', json_encode($data),Config::get('myconfig.AP.secret'));
//       dd($sBodyHash);

        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/webhook',$data);
        dd($req->json());
    }
    public static function get_banks($id)
      {

          $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));
          $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url')."/crossborder/banks/".$id);

          if ($req->status()=="200" && $req->json()!=null)
          {

              $list=$req->json();
              $c="";
              $c=$c."<option value='' >--Select--</option>";
              foreach ($list as $i)
              {

                  $c=$c."<option value='".$i['Id']."' >".$i['Name']."</option>";
              }
              return $c;
          }else
          {
              return "";
          }
         }
    public static function get_branches($id)
    {

        $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));

        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url')."/crossborder/bank/".$id."/branches");


        if ($req->status()=="200")
        {
            $list=$req->json();
            $c="";
            foreach ($list as $i)
            {

                $c=$c."<option value='".$i['BankBranchID']."' >".$i['StateName'].'-'.$i['CityName'].'-'.$i['BankBranchName']."</option>";
            }
            return $c;
        }else
        {
            return "";
        }
    }
    public static function get_type_of_ids($id)
    {
        $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));
        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url')."/crossborder/type-of-ids/".$id);
dd($req->json());
        if ($req->status()=="200")
        {
            $list=$req->json();
            $c="";
            foreach ($list as $i)
            {

                $c=$c."<option value='".$i['BankBranchID']."' >".$i['BankBranchName']."</option>";
            }
            return $c;
        }else
        {
            return "";
        }
    }
    public static function get_cities($id)
    {


        $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));
//
//        $ch = curl_init( 'https://sec.sandbox.aptpay.com/crossborder/cities/PK' );
//        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'AptPayApiKey: K24ggw3iaR5j3f12j672iv1ZsQszrC', 'body-hash:5b51600ce194d88fb7920d72483fb70624f6e274ac5b8ee218eb5254c6e299900fdcfe4791773006d8ba7a4992f360beefa11eac1256ab775643abe5a4cfda1a'] );
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//        $sRes = curl_exec($ch);
//        dd($sRes);
        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url')."/crossborder/cities/".$id);
        dd($req->json());
        if ($req->status()=="200")
        {
            $list=$req->json();
            $c="";
            foreach ($list as $i)
            {

                $c=$c."<option value='".$i['BankBranchID']."' >".$i['BankBranchName']."</option>";
            }
            return $c;
        }else
        {
            return "";
        }
    }
    public static function get_identity($id)
    {


        $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));
//
//        $ch = curl_init( 'https://sec.sandbox.aptpay.com/crossborder/cities/PK' );
//        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'AptPayApiKey: K24ggw3iaR5j3f12j672iv1ZsQszrC', 'body-hash:5b51600ce194d88fb7920d72483fb70624f6e274ac5b8ee218eb5254c6e299900fdcfe4791773006d8ba7a4992f360beefa11eac1256ab775643abe5a4cfda1a'] );
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//        $sRes = curl_exec($ch);
//        dd($sRes);
        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url')."/identities/".$id);
        var_dump($req->json());
        if ($req->status()=="200")
        {
            $list=$req->json();
            $c="";
            foreach ($list as $i)
            {

                $c=$c."<option value='".$i['BankBranchID']."' >".$i['BankBranchName']."</option>";
            }
            return $c;
        }else
        {
            return "";
        }
    }
    public static function get_purposes($id)
    {
        $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));
        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url')."/crossborder/purposes/".$id);

        if ($req->status()=="200")
        {
            $list=$req->json();
            $c="";
            if ($list!=null)
            {
                foreach ($list as $i)
                {

                    $c=$c."<option value='".$i."' >".$i."</option>";
                }
            }

            return $c;
        }else
        {
            return "";
        }
    }
    public static function get_disbursements($id)
    {
        $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));
        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url')."/disbursement-instrument/list/".$id);
    dd($req);

    }
    public static function sendPay(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'amount'=>'required|numeric|min:1|max:100'
        ]);

        if ($validator->fails()) {

            return response()->json(['code'=>'0','message'=>array_values($validator->errors()->messages())], 200);
        }

            if ($request->amount>my_wallet_balance(auth()->user()->id))
            {
                return response()->json(['code'=>0,'message'=>[["Insufficient balance"]]]);
            }

//asassadd

        $sp=new aptpaysendpayment();
        $sp->s_type=$request->p_type;
        $sp->user_id=auth()->user()->id;
        $sp->account_number=$request->accountNumber;
        $sp->type=$request->type;
        $sp->amount=$request->amount;
        $sp->email=$request->email;
        $sp->name=$request->name;
        $sp->transaction_id=rand(1313123,213123213123);
        $sp->status="sent";
        $sp->save();

        $req=null;
        if ($request->type=="CARD")
        {

            $array=explode('/',$request->expirationDate);
            $eDate=trim($array[1],' ').'-'.trim($array[0],' ');
            $eDate="20".$eDate;

            // for card
            $aPostData = [
                'identityId' =>$request->payee_id,
                'amount' => $request->amount,
                'currency' => 'CAD',
                'transactionType'=>'CARD',
                'disbursementNumber' => $request->accountNumber,
                'expirationDate' =>$eDate,
                'referenceId' => str_random(10).$sp->id
            ];
            $sBodyHash = hash_hmac('sha512', json_encode($aPostData),Config::get('myconfig.AP.secret'));

            $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/disbursements/add',$aPostData);

            //            $sp->expiry_date=$request->expirationDate;


        }

        if ($request->type=="EFT")
        {
            $sp->institution_number=$request->bankNumber;
            $sp->transit_number=$request->transitNumber;
            //for EFT
            $aPostData = [
                'identityId' =>$request->payee_id,
                'amount' => $request->amount,
                'currency' => 'CAD',
                'transactionType'=>'EFT',
                'bankNumber'=>$request->bankNumber,
                'branchTransitNumber'=>$request->transitNumber,
                'accountNumber'=>$request->accountNumber,
                'referenceId' => str_random(10).$sp->id
            ];

            $sBodyHash = hash_hmac('sha512',json_encode($aPostData),Config::get('myconfig.AP.secret'));


            $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/disbursements/add',$aPostData);

        }
        if ($request->type=="INTERAC")
        {

            //for EFT
            $aPostData = [
                'identityId' =>$request->payee_id,
                'amount' => $request->amount,
                'currency' => 'CAD',
                'transactionType'=>'INTERAC',
                'referenceId' => str_random(10).$sp->id
            ];

            $sBodyHash = hash_hmac('sha512',json_encode($aPostData),Config::get('myconfig.AP.secret'));


            $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/disbursements/add',$aPostData);

        }
//sasas
        if ($req->status()=="200")
        {
            $data=$req->json();
            $sp->external_id=$data['id'];
            $sp->external_identity=$request->payee_id;
            if ($request->type=="CARD") {
                $sp->expiry_date = $eDate;
            }
            $sp->save();
//            if ($request->p_type=='Wallet')
//            {
                sub_balance_from_wallet(auth()->user()->id,$request->amount);
//            }
            return response()->json(['code'=>'1',"message"=>"Transaction Successfully started","data"=>$data]);

        }
        else
        {
            $req=$req->json();

            $sp->delete();
            return response()->json(['code'=>'0',"message"=>"Transaction  cannot be  started. something going wrong","error"=>$req]);

        }

// for EFT



//            $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/disbursements/add',$aPostData);
//dd($req->json());

//            if ($req->status()=="200"ful())
//            {
//                return response()->json(['code'=>"1","message"=>"Transaction has been processed successfully"]);
//            }
//            else
//            {
//                return response()->json(['code'=>"0","message"=>"Transaction Declined"]);
//            }



    }
    public static function sendWithdrawPay(Request $request)
    {

//asassaddasdasdas

        $sp=new aptpaysendpayment();
        $sp->s_type=$request->p_type;
        $sp->user_id=auth()->user()->id;
        $sp->account_number=$request->accountNumber;
        $sp->type=$request->type;
        $sp->amount=$request->amount;
        $sp->transaction_id=rand(1313123,213123213123);
        $sp->status="sent";
        $sp->save();

        $req=null;
        if ($request->type=="CARD")
        {

            $array=explode('/',$request->expirationDate);
            $eDate=trim($array[1],' ').'-'.trim($array[0],' ');
            $eDate="20".$eDate;

            // for card
            $aPostData = [
                'identityId' =>$request->payee_id,
                'amount' => 1,
                'currency' => 'CAD',
                'transactionType'=>'CARD',
                'disbursementNumber' => $request->accountNumber,
                'expirationDate' =>$eDate,
                'referenceId' => "merchant".$sp->id
            ];

            $sBodyHash = hash_hmac('sha512', json_encode($aPostData),Config::get('myconfig.AP.secret'));

            $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/disbursements/add',$aPostData);

            //            $sp->expiry_date=$request->expirationDate;


        }

        if ($request->type=="EFT")
        {
            $sp->institution_number=$request->bankNumber;
            $sp->transit_number=$request->transitNumber;
            //for EFT
            $aPostData = [
                'identityId' =>$request->payee_id,
                'amount' => 1,
                'currency' => 'CAD',
                'transactionType'=>'EFT',
                'bankNumber'=>$request->bankNumber,
                'branchTransitNumber'=>$request->transitNumber,
                'accountNumber'=>$request->accountNumber,
                'referenceId' => "merchant".$sp->id
            ];

            $sBodyHash = hash_hmac('sha512',json_encode($aPostData),Config::get('myconfig.AP.secret'));


            $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/disbursements/add',$aPostData);

        }
//sasas
        if ($req->status()=="200")
        {
            $data=$req->json();
            $sp->external_id=$data['id'];
            $sp->external_identity=$request->payee_id;
            if ($request->type=="CARD") {
                $sp->expiry_date = $eDate;
            }
            $sp->save();
            return response()->json(['code'=>'1',"message"=>"Transaction Successfully started"]);

        }
        else
        {
            $req=$req->json();

            $sp->delete();
            return response()->json(['code'=>'0',"message"=>"Transaction  cannot be  started. something going wrong","error"=>$req]);

        }

// for EFT



//            $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/disbursements/add',$aPostData);
//dd($req->json());

//            if ($req->status()=="200"ful())
//            {
//                return response()->json(['code'=>"1","message"=>"Transaction has been processed successfully"]);
//            }
//            else
//            {
//                return response()->json(['code'=>"0","message"=>"Transaction Declined"]);
//            }



    }
    public static function aptpayWebhook(Request $request)
    {


        //asdasdadasdaasdasdasdasdasasdasdasd
        $r=new testRequest();
        //        $data=$request->json();

                  $r->content=$request->entity.$request->status;
                   $r->save();
        $r=new testRequest();
        //        $data=$request->json();ljkljkl

        $r->content=json_encode($request->all());
        $r->save();
        $status="(zPAYD-Started)";
        if ($request->has('entity') && $request->entity=="disbursement")
       {


           if ($request->status=="OK")
           {
               $status="Started";
           }
           if ($request->status=="SETTLED")
           {
               $status="APPROVED";
           }
           if ($request->status=="ERROR")
           {
               $status=$request->errorCode;
           }
           if ($request->status=="BUSINESS_REJECTED")
           {
               $status=$request->errorCode;
           }
           if ($request->status=="ERROR_INFO")
           {
               $status=$request->info;
           }
           if ($request->status=="TRANSACTION_CANCELLED")
           {
               $status=$request->status;
           }
           if(aptpaysendpayment::where('external_id',$request->id)->exists())
           {

               $p=aptpaysendpayment::where('external_id',$request->id)->first();
               $p->status=$status;
               $p->save();
           }
           if(aptpaysendpayment::where('external_id',$request->id)->exists())
           {

               $p=aptpaysendpayment::where('external_id',$request->id)->first();
               $p->status=$status;
               $p->save();
           }

           //sdsd

       }
        if(aptpaydebitpayment::where('payment_id_one',$request->id)->exists())
        {
            $p=aptpaydebitpayment::where('payment_id_one',$request->id)->first();
            $p->payment_status_one=$request->status;
            $p->save();
        }
        if(aptpaydebitpayment::where('payment_id',$request->id)->exists())
        {
///asdasdwrwrwrsdadasdadasadasasdassdfsdfsasdasasdasdsadasdasdasd
///
/// asdasd
            $p=aptpaydebitpayment::where('payment_id',$request->id)->first();
            $p->status=$request->status;
            $p->save();

            if($request->status=="SETTLED")
            {
                if ($p->type=="OfferRefund")
                {
                    //adasdasd

                    $o=merchantOffers::find($p->external_id);

                    self::sendInteractForRefund($p->amount,$o->aptpay_identity,$o->id,$p->id);

                }

            }




        }
        //sadasdsdsdfasdasdasdasdasdasd


    }
    public function sendPayView(Request $request,$id)
    {

        $data['temp']=null;
        if ($request->has('external_id'))
        {
            $data['external_id']=merchantOffers::Myown()->where('id',$request->external_id)->first();
        }


        return view('aptpay.sendPayment',$data);
    }
    public function createIdentityView(Request $request)
    {
        $data['external_id']=null;
        if ($request->has('external_id'))
        {
            $data['external_id']=merchantOffers::Myown()->where('id',$request->external_id)->first();
        }

        return view('aptpay.createIdentity',$data);
    }
    public function createIdentityNow(Request $request)
    {


          $minDate=Carbon::parse(today_date())->subYears(18);
          $minDate=$minDate->format('Y-m-d');
        $request->validate([
            'date_of_birth'=>'before:'.$minDate
        ]);
        $data=[
            "individual"=> true,
            "first_name"=> $request->first_name,
            "last_name"=> $request->last_name,
            "street"=>$request->street,
            "email"=>$request->email,
            "city"=> $request->city,
            "zip"=> $request->zip,
            "country"=> $request->country,
            "dateOfBirth"=> $request->date_of_birth,
            "clientId"=>rand(10000000,10000000000)];
        $sBodyHash = hash_hmac('sha512', json_encode($data),Config::get('myconfig.AP.secret'));
//       dd($sBodyHash);

        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/identities/add',$data);

         $temp_id='ok';
         if ($request->has('temp_id'))
         {
             $temp_id=$request->temp_id;
         }
        if ($req->status()=="200")
        {
          $req=$req->json();
          ///asdasd
          $url=$request->next_url.'/'.$req['id'].'?temp_id='.$temp_id;

          if ($request->has('external_id'))
          {
              $url=$url.'?external_id='.$request->external_id;
          }
            if ($request->has('type'))
            {
                $url=$url.'&type='.$request->type;
            }
            $url=$url.'&name='.$request->first_name.' '.$request->last_name.'&email='.$request->email;
         return redirect($url);
        }
        else
        {
            return redirect()->back()->with([
            'toast' => [
                'heading' => 'Message',
                'message' => "Unable to create payee there is something going wrong.",
                'type' => 'error',
            ]
        ]);
        }
    }
    //asdasd
  public static function xb_calculate(Request $request)
  {
      $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));
      $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url').'/crossborder/calculate?paymentMode='.$request->paymentMode.'&receiveCurrency='.$request->receiveCurrency.'&sourceCurrency='.$request->sourceCurrency.'&receiveCountry='.$request->receiveCountry.'&sentAmount='.$request->sentAmount.'&bankId='.$request->bankId.'&receiveAmount='.$request->receiveAmount);
   if ($req->status()=="200")
   {

       return response()->json(['code'=>'1','data'=>$req->json()]);
   }
   else
   {
       return response()->json(['code'=>'0','data'=>$req->json()]);
   }


    }
    public static function addKYC($id)
    {
        //asdasd

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
        $aPostData['identificationFile'] = new \CurlFile($filePath, 'image/png', 'filename.png');


        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'AptPayApiKey: '.Config::get('myconfig.AP.key'), 'body-hash: '.$sBodyHash] );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $aPostData );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $sRes = curl_exec($ch);
        dd($sRes);
        exit;
//adasd
    }
    public function sendPaymentInternationalView()
    {
        return view('aptpay.internationalPayment');
    }
    public static function sendPaymentInternationalNow(Request $request)
    {
        $aPostData = [
            'receiver' => [
                'identityId' => $request->receiver
            ],
            'sender' => [
                'senderId'=>$request->sender,
            ],
            "transaction"=> [
                'amount'=>4,
                "paymentMode"=> "BANK_DEPOSIT",
                "sourceCurrency"=>"PKR",
                "receiveCurrency"=>$request->currency,
                "account"=> $request->account,
                "branch"=>$request->branch,
                "bankId"=>$request->bank,
                "accountType"=> "SAVINGS",
                "purpose"=> "FAMILY_MAINTENANCE"
            ]
        ];

        $sBodyHash = hash_hmac('sha512', json_encode($aPostData),Config::get('myconfig.AP.secret'));

        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/crossborder/transaction/create',$aPostData);

      var_dump($req->json());


    }
    public function index()
    {
        return view('aptpay.index');
    }
    public static function sendSMS($message)
    {
        $data=[
            'to'=>"+38348166607",
            'message'=>$message
        ];
        $sBodyHash = hash_hmac('sha512', json_encode($data),Config::get('myconfig.AP.secret'));
//       dd($sBodyHash);

        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/sms/send',$data);
        dd($req->json());
    }
    public static function validate_bank($id)
    {
        $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));
        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url')."/crossborder/validate-iban/".$id);

        if ($req->status()=="200")
        {
            $list=$req->json();
            $c="";
            if ($list!=null)
            {
                foreach ($list as $i)
                {

                    $c=$c."<option value='".$i."' >".$i."</option>";
                }
            }

            return $c;
        }else
        {
            return "";
        }
    }
    public static function validate_iban($id)
    {


        $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));
        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url')."/crossborder/validate-iban/".$id);
        return response()->json($req->json());

    }
    public static function get_merchant_balance()
    {

        $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));
        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url')."/balance?type=2");
        return response()->json($req->json());

    }
    public static function getProvinces($id)
    {
        $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));
        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url')."/crossborder/validate-iban/".$id);

        if ($req->status()=="200")
        {
            $list=$req->json();
            $c="";
            if ($list!=null)
            {
                foreach ($list as $i)
                {

                    $c=$c."<option value='".$i."' >".$i."</option>";
                }
            }

            return $c;
        }else
        {
            return "";
        }
    }
    public static function createEftDebit($id)
    {
        $aPostData = [
            'identityId' =>$id,
            'amount' => 1,
            'financialInstitutionNumber'=>'002',
            'branchTransitNumber'=>'18382',
            'accountNumber'=>'0934585',
            'currency' => 'CAD',
            'verification'=>False,
            'referenceId' => str_random(10)
        ];
        $sBodyHash = hash_hmac('sha512', json_encode($aPostData),Config::get('myconfig.AP.secret'));

        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/eft-debit/create',$aPostData);
         dd($req->json());
    }
    public static function sendEftDebitView($id)
    {
    return view('aptpay.sendeftdeitrequest');
    }
    public static function sendEftDebitNow(Request $request)
    {
        $request->validate([
            'bank_number'=>'required|min:3',
            'branch_number'=>'required|min:5',
            'account_number'=>'required|min:7',
            'identity_id'=>'required',
            'amount'=>'required|numeric|min:1',
        ]);
        $aPostData = [
            'identityId' =>$request->identity_id,
            'amount' => $request->amount,
            'financialInstitutionNumber'=>$request->bank_number,
            'branchTransitNumber'=>$request->branch_number,
            'accountNumber'=>$request->account_number,
            'currency' => 'CAD',
            'verification'=>False,
            'referenceId' => str_random(10).$request->identity_id
        ];
        $sBodyHash = hash_hmac('sha512', json_encode($aPostData),Config::get('myconfig.AP.secret'));

        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/eft-debit/create',$aPostData);

        if ($req->status()=="200")
       {
           $req=$req->json();
           $no=new aptpaydebitpayment();
           $no->user_id=auth()->user()->id;
           $no->type="Type";
           $no->amount=$request->amount;
           $no->currency="CAD";
           $no->institution_number=$request->bank_number;
           $no->transit_number=$request->branch_number;
           $no->account_number=$request->account_number;
           $no->external_id=$request->temp_id;
           $no->payment_id=$req['id'];
           $no->status="Started";
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
    public static function sendInteractForRefund($amount,$identity,$offer_id,$debit_id)
    {

        $offer=merchantOffers::find($offer_id);

        $debit=aptpaydebitpayment::find($debit_id);
        $sp=new aptpaysendpayment();
        $sp->s_type="Refund-Deposit";
        $sp->user_id=$offer->user_id;
        $sp->type="INTERAC";
        $sp->amount=$amount;
        $sp->email=$offer->email;
        $sp->name=$offer->name;
        $sp->transaction_id=rand(1313123,213123213123);
        $sp->status="zPAYD-Started";
        $sp->save();
        $aPostData = [
            'identityId' =>414052763039,
            'amount' => $amount,
            'currency' => 'CAD',
            'transactionType'=>'INTERAC',
            'referenceId' => str_random(10).$sp->id
        ];
        $sBodyHash = hash_hmac('sha512',json_encode($aPostData),Config::get('myconfig.AP.secret'));
        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/disbursements/add',$aPostData);


        if ($req->status()=="200" || $req->status()=="201") {
            $req = $req->json();
            $debit->payment_id_one = strval($req['id']);
            $debit->payment_status_one ="zPAYD-Started";
            $sp->external_id= strval($req['id']);
            $sp->status ="zPAYD-Started";
            $debit->save();
            $sp->save();
            //asdasdasd

        }
        else
        {
            $req = $req->json();

            $debit->payment_id_one ='';
            if (isset($req['errors']) && isset($req['errors'][0]))
            {
                $debit->reason=$req['errors'][0];
            }
  if (isset($req['error_code']))
  {
      $debit->reason=$debit->reason.' ('.$req['error_code'].')';
  }
            $debit->payment_status_one ="zPAYD-Not-Started";
            $sp->external_id='';
            $sp->status ="zPAYD-Not-Started";
            $debit->save();
            $sp->save();
        }
    }
}
