<?php

namespace App\Http\Controllers;

use App\Models\packageTransaction;
use App\Notifications\twostepverificationcode;
use App\Notifications\twostepverificationcodeonemail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TwoStepVerificationController extends Controller
{

    public function email_2FA()
    {

        if (Request::capture()->expectsJson())
        {

            $user=auth()->user();
            $data['user']=$user;

            $data['code']=rand(10000,20000);
            $user->code=$data['code'];

            $user->notify(new twostepverificationcodeonemail($data));

            $user->save();
            return response()->json(['message'=>'Code successfully Sent to registered email.']);
        }
        if (session('login_try',0)>3)
        {
            Session::put('login_try',0);
        }
        if (session('login_try',0)==0)
        {
            $data['code'] =str_random(20);

            $user=auth()->user();
            $data['user']=$user;
            $data['code']=rand(10000,20000);
            Session::put('Code_email2FA',$data['code']);

            $user->notify(new twostepverificationcodeonemail($data));
        }


        return view('Security.verifyemail2FACode',$data);
    }
    public function index()
    {


        if (Request::capture()->expectsJson())
        {

            $user=auth()->user();
            $data['user']=$user;

            $data['code']=rand(10000,20000);
            $user->code=$data['code'];

            $user->notify(new twostepverificationcode($data));

            $user->save();
            return response()->json(['message'=>'Code successfully Sent']);
        }
        if (session('login_try',0)>3)
        {
            Session::put('login_try',0);
        }
        if (session('login_try',0)==0)
        {
            $data['code'] =str_random(20);

            $user=auth()->user();
            $data['user']=$user;
            $data['code']=rand(10000,20000);
            Session::put('Code_2FA',$data['code']);

            $user->notify(new twostepverificationcode($data));
        }


          return view('Security.verify2FACode',$data);
    }
    public function Verify2FACode(Request $request)
    {
        if (Request::capture()->expectsJson())
        {
            $user=auth()->user();
            if ($user->code==null || $user->code!=$request->code)
            {
                   return response()->json(['code'=>409,'message'=>'Code is not correct']);
            }

            $user->code=null;
            $user->save();
            return response()->json(['code'=>200,'message'=>'Two Step verification Process Completed']);

        }

        if (session('Code_2FA')!=$request->code)
        {
            Session::put('login_try',\session('login_try',0)+1);

            if (session('login_try')>3)
            {
                auth()->logout();
                return redirect('login')->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'Sorry Login Again',
                        'type' => 'error',
                    ]
                ]);
            }
            else
            {

                return back() ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'Code is incorrect',
                        'type' => 'error',
                    ]
                ]);
            }

        }
        else
        {

            Session::put('login_2FA',true);

            return redirect('dashboard');
        }

    }
    public function VerifyEmail2FACode(Request $request)
    {
        if (Request::capture()->expectsJson())
        {
            $user=auth()->user();
            if ($user->code==null || $user->code!=$request->code)
            {
                return response()->json(['code'=>409,'message'=>'Code is not correct']);
            }

            $user->code=null;
            $user->save();
            return response()->json(['code'=>200,'message'=>'Two Step verification Process Completed']);

        }

        if (session('Code_email2FA')!=$request->code)
        {
            Session::put('login_try',\session('login_try',0)+1);

            if (session('login_try')>3)
            {
                auth()->logout();
                return redirect('login')->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'Sorry Login Again',
                        'type' => 'error',
                    ]
                ]);
            }
            else
            {

                return back() ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'Code is incorrect',
                        'type' => 'error',
                    ]
                ]);
            }

        }
        else
        {

            Session::put('login_email_2FA',true);

            return redirect('dashboard');
        }

    }
}
