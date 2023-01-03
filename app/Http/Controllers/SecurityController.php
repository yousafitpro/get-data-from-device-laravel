<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\sendPhoneVerificationCode;
use App\Notifications\twostepverificationcode;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Support\Facades\Notification;
use Session;

class SecurityController extends Controller
{
    public function page()
    {
        return view('Security.page');
    }
    public function verify_phone_number(Request $request)
    {

        $users=User::where('id',auth()->user()->id)->get();
        foreach ($users as $user)
        {
            $user->phone=$request->phone;
        }

        $data['code']=rand(10000,20000);
        $data['user']=auth()->user();
        $data['phone']=$request->phone;
        if (Request::capture()->expectsJson())
        {

            $user->code=$data['code'];


            Notification::send($users,new sendPhoneVerificationCode($data));
            $user->save();
            return response()->json(['message'=>'Code successfully Sent']);
        }
        Session::put('Code_Phone_Verify',$data['code']);
        Session::put('Code_Phone',$request->phone);


        try {
            Notification::send($users,new sendPhoneVerificationCode($data));
        }
        catch (\Exception $e)
        {
            dd($e);
        }
      return view('Security.verifyPhoneCode');
    }
    public function verify_phone_number_step_2(Request $request)
    {
        if (Request::capture()->expectsJson())
        {
            $user = User::find(auth()->user()->id);
            if ($user->code!=null && $user->code==$request->code) {

                $user->phone = $request->phone;
                $user->code==null;
                $user->save();
                UserSetting::where('user_id', auth()->user()->id)->update([
                    'is_phone_verified' => 'true',
                    'is_two_step_enabled' => 'true'
                ]);
                return response()->json(['code'=>200,'message'=>'Phone verification Process Completed']);

            }
            else
            {
                return response()->json(['code'=>409,'message'=>'Code is not correct']);
            }

        }
       if (session('Code_Phone_Verify','0')==$request->code)
       {
             $user=User::find(auth()->user()->id);
             $user->phone=session('Code_Phone');
             $user->save();
           UserSetting::where('user_id',auth()->user()->id)->update([
              'is_phone_verified'=>'true',
              'is_two_step_enabled'=>'true'
           ]);
           return redirect(url('security/page'))->with([
               'toast' => [
                   'heading' => 'Message',
                   'message' => 'Two Step verification Successfully Enabled',
                   'type' => 'success',
               ]
           ]);
       }
        return redirect()->back()->with([
            'toast' => [
                'heading' => 'Message',
                'message' => 'Code is Invalid',
                'type' => 'error',
            ]
        ]);
    }
    public function disable_2fa(Request $request)
    {
        UserSetting::where('user_id',auth()->user()->id)->update([
            'is_two_step_enabled'=>'false'
        ]);
        if(Request::capture()->expectsJson())
        {
            return response()->json(['message'=>'2FA Successfully Disabled']);
        }
        return redirect()->back()->with([
            'toast' => [
                'heading' => 'Message',
                'message' => 'Two Step Verification Success fully Disabled',
                'type' => 'success',
            ]
        ]);
    }
}
