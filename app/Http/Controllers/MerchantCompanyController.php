<?php

namespace App\Http\Controllers;

use App\Models\aptpaydebitpayment;
use App\Models\aptpaysendpayment;
use App\Models\Merchant\merchantCompany;
use App\Models\Merchant\merchantOffers;
use App\Models\User;
use App\Notifications\clientSubmittedThePayment;
use App\Notifications\merchantprofilecreated;
use App\Notifications\sendMerchantOffer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

class MerchantCompanyController extends Controller
{
    public $SRC = 'images/profile/';
    public function companies()
    {
        $data['list']=merchantCompany::all();

        return view('merchant.company.index',$data);
    }
    public function depositHistory($id)
    {
        $c=merchantCompany::find($id);
        $u=User::find($c->user_id);

        $data['list']=aptpaysendpayment::where('s_type','Company-Deposit')->where('user_id',$u->id)->get();
        return view('merchant.company.depositHistory',$data);
    }
    public function add()
    {
        return view('merchant.company.add');
    }
    public function edit($id)
    {
        $data['company']=merchantCompany::find($id);

        return view('merchant.company.edit',$data);
    }

    public function create(Request $request)
    {


        $minDate=Carbon::parse(today_date())->subYears(18);
        $minDate=$minDate->format('Y-m-d');
        $request->validate([
            'email'=>'required|unique:users',
            'currency'=>'required',
            'short_name'=>"required|max:30",
            'phone'=>"required",
            'long_name'=>"required",
            'short_details'=>"required",
            'address'=>"required",
            'password'=>"required|min:8",
            'commission'=>"required|numeric:min:1",
            'confirm_password'=>"required|min:8|same:password",
            'zipcode'=>"required",
            'city'=>"required",
            'country'=>"required"
        ]);
        if ($request->country=="US")
        {
            $request->validate([
                'province'=>"required"
            ]);
        }
        $data=[];
        if ($request->bType=="company")
        {
            $request->validate([
                'typeOfBusiness'=>"required",
                'dbaname'=>"required"
            ]);
            $data=[
                "individual"=> false,
                "street"=>$request->address,
                "email"=>$request->email,
                "phone"=>$request->phone,
                "city"=> $request->city,
                "zip"=> $request->zipcode,
                "name"=> $request->short_name,
                "country"=> $request->country,
                "clientId"=>rand(10000000,10000000000),
                "typeOfBusiness"=>$request->typeOfBusiness,
                "dbaname"=>$request->dbaname];
        }
        if ($request->bType=="personal")
        {
            $request->validate([
                'first_name'=>"required",
                'last_name'=>"required",
                'dateofbirth'=>'required|before:'.$minDate,

            ]);
            $data=[
                "individual"=> true,
                "first_name"=> $request->first_name,
                "last_name"=> $request->last_name,
                "street"=>$request->address,
                "email"=>$request->email,
                "phone"=>$request->phone,
                "city"=> $request->city,
                "zip"=> $request->zipcode,
                "country"=> $request->country,
                "dateOfBirth"=> $request->dateofbirth,
                "clientId"=>rand(10000000,10000000000)];
        }

        if (User::where('email',$request->email)->exists())
        {

            return back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'This email address is already registered',
                        'type' => 'danger',
                    ]
                ])->withInput();
        }

        $sBodyHash = hash_hmac('sha512', json_encode($data),Config::get('myconfig.AP.secret'));
//       dd($sBodyHash);

        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/identities/add',$data);



        if ($req->status()=="200" || $req->status()=="201") {
            $req = $req->json();
            $image = $request->file('avatar');
            $logo = "";

            if ($image) {
                $logo = saveImage($image, $this->SRC);
            }

            $data = $request->except(['_token']);

            $user = new User();
            $user->password = bcrypt($request->password);
            $user->email = $request->email;
            $user->name = $request->short_name;
            $user->avatar = $logo;

            $user->wallet_balance = encrypt(0);
            $user->address = $request->address;
            $user->assignRole('company');
            $user->save();


            $c = new merchantCompany();
            $c->short_name = $request->short_name;
            $c->phone = strval($request->phone);
            $c->province = $request->province;
            $c->long_name = $request->long_name;
            $c->currency = $request->currency;
            $c->commission = $request->commission;
            $c->short_details = $request->short_details;
            if ($request->bType == "personal") {

                $c->first_name=$request->first_name;
                $c->last_name=$request->last_name;
                $c->dateofbirth=$request->dateofbirth;
            }
            if ($request->bType == "company") {

                $c->dbaname = $request->dbaname;
                $c->natureOfBusiness = $request->natureOfBusiness;
                $c->typeOfBusiness = $request->typeOfBusiness;
                $c->bType = $request->bType;
            }

            $c->user_id=$user->id;
            $c->zipcode=$request->zipcode;
            $c->street=$request->address;
            $c->user_account_abr=$request->user_account_abr;
            $c->is_has_eft=$request->is_has_eft;
            $c->city=$request->city;
            $c->external_identity=strval($req['id']);
            $c->country=$request->country;

            $c->save();
            $mailData['name']=$request->short_name;
            $mailData['image_url']=auth()->user()->avatar();
            $delay = now()->addSeconds(10);
            //        $filePath=asset($c->image);
//        $user=User::find($c->user_id);
//


            Notification::route('mail',$request->email)->notify(new merchantprofilecreated($mailData));
            try {
                $aPostData = [
                    'api_key'=>config('myconfig.App.api_key'),
                    'name'=>$request->short_name,
                    'accountabbreviation'=>$request->user_account_abr,
                    'aptpay_id'=>strval($req['id']),
                    'nickname'=>$request->short_name,
                    'commission'=>$c->commission,
                    'currency'=>$c->currency,
                    'company_id'=>$c->id,
                ];
                $ch = curl_init( config('myconfig.App.zpayd_url').'/api/talpay/add_service_from_outside' );
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data'
                ));

                $aPostData['image'] = new \CurlFile($user->avatar(), 'image/png', 'filename.png');
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $aPostData );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $sRes = curl_exec($ch);
            }catch (\Exception $e)
            {

            }
            ///asdasdasdaSsaasdasd
            return back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => $request->short_name.' Successfully Added',
                        'type' => 'danger',
                    ]
                ]);
        }
        else
        {
            $errors=$req->json();
            $errors=array_values($errors)[0];
            $errors=array_values($errors)[0];
            $errors=[$errors];

            return redirect()->back()->withInput()->withErrors($errors);

        }

    }
    public function updateCompanyBank(Request $request,$id)
    {

        $request->validate([
            'institution_number'=>"required",
            'branch_number'=>"required",
            'account_number'=>"required",
        ]);
        $c=merchantCompany::find($id);
        $c->institution_number=$request->institution_number;
        $c->branch_number=$request->branch_number;
        $c->account_number=$request->account_number;
        $c->save();

        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Bank Info Successfully Updated',
                    'type' => 'success',
                ]
            ]);

    }
    public function updateCompanyInterBank(Request $request,$id)
    {


        $request->validate([
            'interBank'=>"required",
            'interAccount'=>"required",
        ],[
                'interBank.required'=>"Please select a bank",
                'interBranch.required'=>"Please select a branch",
                'interAccount.required'=>"Please enter IBAN/Account",
            ]
        );
        $c=merchantCompany::find($id);
        $c->interBank=$request->interBank;
        $c->interBranch=$request->interBranch;
        $c->interAccount=$request->interAccount;
        $c->interBankName=$request->interBankName;
        $c->save();
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Bank Info Successfully Updated',
                    'type' => 'success',
                ]
            ]);


    }
    public function updateCompanyCard(Request $request,$id)
    {

        $array=explode('/',$request->expirationDate);
        $eDate=trim($array[1],' ').'-'.trim($array[0],' ');
        $eDate=ENVController::$year_period.$eDate;
        $c=merchantCompany::find($id);
        $c->card_number=encrypt($request->disbursementNumber);
        $c->card_expiration_date=$eDate;
        $c->card_cvc=$request->card_cvc;
        $c->save();
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Debit-Card Info Successfully Updated',
                    'type' => 'success',
                ]
            ]);
    }
    public function update(Request $request,$id)
    {
///asdasdasd
/// d
///asdasdasd
///




        $minDate=Carbon::parse(today_date())->subYears(18);
        $minDate=$minDate->format('Y-m-d');
        $request->validate([
            'short_name'=>"required|max:30",
            'long_name'=>"required",
            'short_details'=>"required",
            'address'=>"required",
            'commission'=>"required|numeric:min:1",
            'zipcode'=>"required",
            'city'=>"required",
            'country'=>"required",
            'phone'=>"required",
        ]);
        if ($request->country=="US")
        {
            $request->validate([
                'province'=>"required"
            ]);
        }
        $data=[];
        if ($request->bType=="company")
        {
            $request->validate([
                'typeOfBusiness'=>"required",
                'dbaname'=>"required",
            ]);
            $data=[
                "individual"=> false,
                "street"=>$request->address,
                "email"=>$request->email,
                "phone"=>$request->phone,
                "city"=> $request->city,
                "zip"=> $request->zipcode,
                "name"=> $request->short_name,
                "country"=> $request->country,
                "clientId"=>rand(10000000,10000000000),
                "provinceOfRegistration"=>$request->provinceOfRegistration,
                "countryOfRegistration"=>$request->countryOfRegistration,
                "province"=>$request->province,
                "dateOfIncorporation"=>$request->dateOfIncorporation,
                "typeOfBusiness"=>$request->typeOfBusiness,
                "dbaname"=>$request->dbaname];


        }
        if ($request->bType=="personal")
        {
            $request->validate([
                'first_name'=>"required",
                'last_name'=>"required",
                'dateofbirth'=>'required|before:'.$minDate,

            ]);
            $data=[
                "individual"=> true,
                "first_name"=> $request->first_name,
                "last_name"=> $request->last_name,
                "street"=>$request->address,
                "email"=>$request->email,
                "city"=> $request->city,
                "phone"=>$request->phone,
                "zip"=> $request->zipcode,
                "country"=> $request->country,
                "dateOfBirth"=> $request->dateofbirth,
                "clientId"=>rand(10000000,10000000000)];
        }
        if ($request->country=="US")
        {
            $data['province']=$request->province;
        }

        $sBodyHash = hash_hmac('sha512', json_encode($data),Config::get('myconfig.AP.secret'));
//       dd($sBodyHash);

        $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/identities/add',$data);



        if ($req->status()=="200" || $req->status()=="201")
        {
            $image = $request->file('avatar');
            $logo="";
            $c=merchantCompany::find($id);
            $user=User::find($c->user->id);
            if ($image) {
                $logo = saveImage($image, $this->SRC);
                $user->avatar=$logo;
                $user->save();
            }

            $data=$request->except(['_token']);

            $c->short_name=$request->short_name;
            $c->province=$request->province;
            $c->long_name=$request->long_name;
            $c->phone=strval($request->phone);
            $c->commission=$request->commission;
            $c->short_details=$request->short_details;
            if ($request->bType == "personal") {
//asdas
                $c->first_name=$request->first_name;
                $c->last_name=$request->last_name;
                $c->dateofbirth=$request->dateofbirth;
            }
            if ($request->bType == "company") {

                $c->dbaname = $request->dbaname;

                $c->typeOfBusiness = $request->typeOfBusiness;
                $c->bType = $request->bType;
            }

            $c->bType=$request->bType;
            $c->zipcode=$request->zipcode;
            $c->street=$request->address;
            $c->city=$request->city;
            $c->user_account_abr=$request->user_account_abr;
            $c->is_has_eft=$request->is_has_eft;
            $c->currency = $request->currency;

            $c->country=$request->country;
            $c->external_identity=strval($req['id']);
            $c->save();


            $user->name=$request->short_name;
            $user->address=$request->address;
            $user->save();
            // adding COmpany to zpayd


            try {
                $aPostData = [
                    'api_key'=>config('myconfig.App.api_key'),
                    'name'=>$request->short_name,
                    'accountabbreviation'=>$request->user_account_abr,
                    'aptpay_id'=>strval($req['id']),
                    'nickname'=>$request->short_name,
                    'currency'=>$c->currency,
                    'commission'=>$c->commission,
                    'company_id'=>$c->id,
                ];
                //asdasdadasdasdsadasd
                $ch = curl_init( config('myconfig.App.zpayd_url').'/api/talpay/add_service_from_outside' );
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data'
                ));
                $aPostData['image'] = new \CurlFile($user->avatar(), 'image/png', 'filename.png');
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $aPostData );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $sRes = curl_exec($ch);


            }catch (\Exception $e)
            {

            }


            return back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => $request->short_name.' Successfully Updated',
                        'type' => 'success',
                    ]
                ]);
        }
        else
        {
            $errors=$req->json();
            $errors=array_values($errors)[0];
            $errors=array_values($errors)[0];
            $errors=[$errors];

            return redirect()->back()->withInput()->withErrors($errors);
        }

    }
    public function block($id)
    {
        $c=merchantCompany::find($id);
        $u=User::find($c->user_id);
        $u->status="Blocked";
        $u->save();
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => $c->short_name.' Successfully Blocked',
                    'type' => 'success',
                ]
            ]);
    }
    public function unblock($id)
    {
        $c=merchantCompany::find($id);
        $u=User::find($c->user_id);
        $u->status="Active";
        $u->save();
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => $c->short_name.' Successfully Unblocked',
                    'type' => 'success',
                ]
            ]);
    }
    public function brief_detail($id)
    {
        $data['company']=merchantCompany::find($id);
        $data['user']=User::find($data['company']->user_id);
        $data['offers']=merchantOffers::where('user_id',$data['company']->user_id)->get();
        $data['unclearedPayments']=merchantOffers::where('user_id',$data['company']->user_id)->where('is_funds_cleared','Not-Cleared')->where('status','APPROVED')->get();
        $data['refunds']=aptpaydebitpayment::where("type",'OfferRefund')->where('user_id',$data['company']->user_id)->get();
        foreach($data['refunds'] as $r)
        {
            $r->offer=merchantOffers::find($r->external_id);
        }
        return view('merchant.company.brief_details',$data);
    }
    public function send_money_to_company(Request $request,$id)
    {

        $c=merchantCompany::find($id);

        $u=User::find($c->user_id);

        if (my_wallet_balance($u->id)<$request->amount)
        {
            return back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'insufficient company balance',
                        'type' => 'danger',
                    ]
                ]);
        }

        //adasd
        $sp=new aptpaysendpayment();
        $sp->s_type="Company-Deposit";
        $sp->user_id=$u->id;
        $sp->type=$request->type;
        $sp->currency=$c->currency;

        $sp->amount=decrypt($c->user->wallet_balance);
        $sp->email=$u->email;
        $sp->name=$c->first_name.' '.$c->last_name;
        $sp->transaction_id=rand(1313123,213123213123);
        $sp->status="zPAYD-Started";
        $sp->save();

        $req=null;

        if ($c->country=="CA")
        {

            if ($request->type=="CARD")
            {
                dd('no');
//                return back()
//                    ->with([
//                        'toast' => [
//                            'heading' => 'Message',
//                            'message' => 'Money Successfully Sent To Company',
//                            'type' => 'success',
//                        ]
//                    ]);
//                $sp->account_number= $c->card_number;
//
//            $array=explode('/',$c->expirationDate);
//            $eDate=trim($array[1],' ').'-'.trim($array[0],' ');
//            $eDate="20".$eDate;

                // for card
                $aPostData = [
                    'identityId' =>$c->external_identity,
                    'amount' => decrypt($c->user->wallet_balance),
                    'currency' => 'CAD',
                    'transactionType'=>'CARD',
                    'disbursementNumber' => decrypt($c->card_number),
                    'expirationDate' =>$c->card_expiration_date,
                    'referenceId' => str_random(10).$sp->id
                ];
                $sBodyHash = hash_hmac('sha512', json_encode($aPostData),Config::get('myconfig.AP.secret'));

                $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/disbursements/add',$aPostData);

                //            $sp->expiry_date=$request->expirationDate;


            }

            if ($request->type=="EFT")
            {
//asdasd
                if($c->institution_number==null || $c->branch_number ==null || $c->account_number ==null)
                return back()
                    ->with([
                        'toast' => [
                            'heading' => 'Message',
                            'message' => 'Please Setup EFT Credentials',
                            'type' => 'success',
                        ]
                    ]);
                $sp->institution_number=$c->institution_number;
                $sp->transit_number=$c->branch_number;
                $sp->account_number= $c->account_number;
                //for EFT
                $aPostData = [
                    'identityId' =>$c->external_identity,
                    'amount' => decrypt($c->user->wallet_balance),
                    'currency' =>'CAD',
                    'transactionType'=>'EFT',
                    'bankNumber'=>$c->institution_number,
                    'branchTransitNumber'=>$c->branch_number,
                    'accountNumber'=>$c->account_number,
                    'referenceId' => str_random(10).$sp->id
                ];

                $sBodyHash = hash_hmac('sha512',json_encode($aPostData),Config::get('myconfig.AP.secret'));


                $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/disbursements/add',$aPostData);

            }
            if ($request->type=="INTERAC")
            {

                //for EFT  asdasd
                $aPostData = [
                    'identityId' =>$c->external_identity,
                    'amount' => decrypt($c->user->wallet_balance),
                    'currency' => 'CAD',
                    'transactionType'=>'INTERAC',
                    'referenceId' => str_random(10).$sp->id
                ];

                $sBodyHash = hash_hmac('sha512',json_encode($aPostData),Config::get('myconfig.AP.secret'));


                $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/disbursements/add',$aPostData);

            }

            if ($req->status()=="200" || $req->status()=="201")
            {
                //asdasdsSSASDSA
                $req=$req->json();
                $sp->external_id=strval($req['id']);
                $sp->save();
                $sp->institution_number=$c->institution_number;
                $sp->transit_number=$c->branch_number;
                $sp->account_number=$c->account_number;
                $sp->save();
                $message=$request->amount." ( ".$c->currency." ) is on its way to you. The money should be in the bank account in 1-2 business days.";
                sub_balance_from_wallet($u->id,$request->amount,$message,'company-deposit');
                return back()
                    ->with([
                        'toast' => [
                            'heading' => 'Message',
                            'message' => 'Money Successfully Sent To Company',
                            'type' => 'success',
                        ]
                    ]);
            }else
            {
                $sp->delete();
                dd($req->json());

                $message='Payment Cannot be Started';
                if (isset($req['errors']) && isset($req['errors'][0]))
                {
                    $message=$req['errors'][0];
                }
                return back()
                    ->with([
                        'toast' => [
                            'heading' => 'Message',
                            'message' => $message,
                            'type' => 'danger',
                        ]
                    ]);
            }
        }
        else
        {
            dd('no');
            if($c->interAccount==null  || $c->interBank==null)
            {
                return back()
                    ->with([
                        'toast' => [
                            'heading' => 'Message',
                            'message' =>"Please setup banking details for this company",
                            'type' => 'danger',
                        ]
                    ]);
            }
            $sBodyHash = hash_hmac('sha512', null,Config::get('myconfig.AP.secret'));
            $reqx=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->get(Config::get('myconfig.AP.url').'/crossborder/calculate?paymentMode=BANK_DEPOSIT&receiveCurrency='.$c->currency.'&sourceCurrency=CAD'.'&receiveCountry='.$c->country.'&sentAmount=0&bankId='.$c->interBank.'&receiveAmount='.my_wallet_balance($c->user_id));
            if ($reqx->status()=="200")
            {

                $data=$reqx->json();
//asdasd
                $aPostData = [
                    'receiver' => [
                        'identityId' => $c->external_identity
                    ],
                    'sender' => [
                        'senderId'=>config('myconfig.AP.sender_id'),
                    ],
                    "transaction"=> [
                        'amount'=>$data['sentAmount'],
                        "paymentMode"=> "BANK_DEPOSIT",
                        "sourceCurrency"=>"CAD",
                        "receiveCurrency"=>$c->currency,
                        "account"=> $c->interAccount,
                        "branch"=>$c->interBranch,
                        "bankId"=>$c->interBank,
                        "accountType"=> "SAVINGS",
                        "purpose"=> "FAMILY_MAINTENANCE"
                    ]
                ];

                $sBodyHash = hash_hmac('sha512', json_encode($aPostData),Config::get('myconfig.AP.secret'));

                $req=Http::withHeaders(['AptPayApiKey'=>Config::get('myconfig.AP.key'),'body-hash'=>$sBodyHash])->post(Config::get('myconfig.AP.url').'/crossborder/transaction/create',$aPostData);
//adasd
                if ($req->status()=="200")
                {
                    //asdasdsSSASDSA
                    $req=$req->json();
                    $sp->external_id=strval($req['id']);
                    $sp->save();
                    $sp->institution_number=$c->interBank;
                    $sp->transit_number=$c->interBranch;
                    $sp->account_number=$c->interAccount;
                    $sp->save();
                    $message=$request->amount." ( ".$c->currency." ) is on its way to you. The money should be in the bank account in 1-2 business days.";
                    sub_balance_from_wallet($u->id,$request->amount,$message,'company-deposit');
                    return back()
                        ->with([
                            'toast' => [
                                'heading' => 'Message',
                                'message' => 'Money Successfully Sent To Company',
                                'type' => 'success',
                            ]
                        ]);
                }else
                {
                    $sp->delete();
                    dd($req->json());

                    $message='Payment Cannot be Started';
                    if (isset($req['errors']) && isset($req['errors'][0]))
                    {
                        $message=$req['errors'][0];
                    }
                    return back()
                        ->with([
                            'toast' => [
                                'heading' => 'Message',
                                'message' => $message,
                                'type' => 'danger',
                            ]
                        ]);
                }
            }
            else
            {
                return response()->json(['code'=>'0','data'=>$req->json()]);
            }

        }

    }
    public function mylink()
    {

        return view('merchant.company.mylink');
    }
    public function receive_link_paymnet_view($id)
    {

        $data['company']=merchantCompany::find(zpayd_decrypt($id));
        if ($data['company']->user->status=="Blocked")
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
        return view('merchant.offers.create_link_paymnet_payee',$data);
    }
    public function change_password(Request $request)
    {
        $c=merchantCompany::find($request->company_id);
        $u=User::find($c->user_id);
        $u->password=bcrypt($request->password);
        $u->save();
        return redirect()->back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Password Successfully Changed',
                    'type' => 'success',
                ]
            ]);
    }
}
