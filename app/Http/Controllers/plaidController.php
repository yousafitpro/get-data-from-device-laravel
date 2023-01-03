<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\LocBankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class plaidController extends Controller
{
    public function getBankHtmlItem(Request $request)
    {

        $res=my_bank($request->token);

        return view('ajax-components.bank')->with(['bank_name'=>$request->bank_name,'title'=>$request->title,'res'=>$res,"item"=>$request->item,"type"=>$request->type]);
    }
    public function Get_Link_Token(Request $request)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('myconfig.PL.url').'/link/token/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
  "client_id": "'.config('myconfig.PL.cid').'",
  "secret": "'.config('myconfig.PL.key').'",
  "client_name": "Insert Client name here",
  "country_codes": ["CA"],
  "language": "en",
  
  "user": {
    "client_user_id": "u'.auth()->user()->id.'"
  },
  "products": ["auth"]
}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response=json_decode($response);

      return response()->json(['link_token'=>$response->link_token]);



    }
    public function Get_Access_Token(Request $request)
    {

        if (BankAccount::where('title',$request->account_title)->where('deleted_at','==',null)->where('bank_id',$request->bank_id)->exists()
        || LocBankAccount::where('title',$request->account_title)->where('deleted_at','==',null)->where('bank_id',$request->bank_id)->exists()
        )
        {
            return response(['message'=>"You have Already an Account"]);
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('myconfig.PL.url').'/item/public_token/exchange',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
  "client_id": "'.config('myconfig.PL.cid').'",
  "secret": "'.config('myconfig.PL.key').'",
  "public_token": "'.$request->public_token.'"
}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response=json_decode($response);
        $user=User::find(auth()->user()->id);
        if($request->type=="loc")
        {
            $acc=new LocBankAccount();
            $count=LocBankAccount::where("user_id",auth()->user()->id)->where('is_primary',true)->get()->count();
            if ($count==0)
            {
                $acc->is_primary=true;
            }

        }
        else
        {
            $acc=new BankAccount();
            $count=BankAccount::where("user_id",auth()->user()->id)->where('is_primary',true)->get()->count();
            if ($count==0)
            {
                $acc->is_primary=true;
            }
        }
        $acc->user_id=auth()->user()->id;

        $acc->access_token=$response->access_token;
        $acc->nic_name=$request->account_title;
        $acc->title=$request->account_title;
        $acc->bank_id=$request->bank_id;
        $acc->bank_name=$request->bank_name;
        $acc->save();
        return response()->json(['data'=>$response]);
    }

}
