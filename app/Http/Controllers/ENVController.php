<?php

namespace App\Http\Controllers;

use App\Events\newuserRegistered;
use App\Helper\myHelper;
use App\Models\etransfer;
use App\Models\sendandrequestfund;
use App\Models\User;
use App\Models\userNote;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ENVController extends Controller
{

    public static $sqaure_url='https://connect.squareup.com';
    public static $access_token='EAAAELUxPQzvAhx8BC5duVu---B4qkfwB6DT2hS8_zdsq9vL_VWNazCa-B_2uKbj';
    public static function notificationTypes()
    {
        return [0=>[
            'name'=>'new_bill_added',
            'title'=>'New Bill Added ?'
        ],
            1=>[
                'name'=>'new_accounting_service_added',
                'title'=>'New Service has been linked ?'
            ],
            2=>[
                'name'=>'two_step_verification',
                'title'=>'Two Step Verification ?'
            ]];
    }
    public static $maxBillAmountLimit=2000;
    public static $year_period=20;
    public static function beforeLogin($user)
    {
        if ($user->business_name==null)
        {
            $user->business_name=str_random(20);
            $user->save();
        }
        if (!UserSetting::where(['user_id'=>$user->id])->exists())
        {
            UserSetting::create(['user_id'=>$user->id]);
        }
        if (!userNote::where(['user_id'=>$user->id])->exists())
        {
            userNote::create(['user_id'=>$user->id]);
        }
        if (!etransfer::where(['user_id'=>$user->id])->exists())
        {
            $uername=myHelper::createSimpleString($user->name);
            $uername=$uername.$user->id.'@etransfer-zpayd.com';
            etransfer::create([
                'user_id'=>$user->id,
                'security_question'=>"Question-".$user->id,
                'unique_id'=>uniqid('user'),
                'username'=>$uername
            ]);
        }
        self::RegisterUserOnSqaure($user);
//        railzController::getToken($user);
        functionsController::createUserNotifications($user);
    }
    public static function RegisterUserOnSqaure($user)
    {
        if ($user->sqaure_customer_id==null)
        {
            $req= Http::withToken(ENVController::$access_token)->post(ENVController::$sqaure_url.'/v2/customers',[
                'idempotency_key'=>str_random(20),
                "given_name"=> $user->name,
                "family_name"=> $user->name,
                "company_name"=> $user->name,
                "nickname"=> $user->name,
                "email_address"=>$user->email,
//                "phone_number"=> null,
                "reference_id"=> 'u'.$user->id,
                "note"=> "A New User",
                "birthday"=> "2008-03-05T00:00:00-00:00"
            ]);


            if ($req->status()=='200')
            {
                $req=$req->json();

                $user->sqaure_customer_id=$req['customer']['id'];
                $user->save();
            }
        }

    }
    public static function afterRegisterUser($user)
    {
        sendandrequestfund::where(['receiver_username'=>$user->email,'receiver_id'=>0])->update([
            'receiver_username'=>null,
            'receiver_id'=>$user->id
        ]);
        $data['user']=$user;
        $mUser=User::find($user->id);
        $mUser->wallet_balance=bcrypt(0);
        $mUser->save();
        event(new newuserRegistered($data));
    }

}
