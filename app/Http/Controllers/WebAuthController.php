<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetLinkMaill;
use App\Models\User;
use App\Notifications\passwordChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;


class WebAuthController extends Controller
{
    public function reset_email()
    {

        return view('auth.passwords.email');
    }
    public function reset_email_send(Request $request)
    {



        if (!User::where('email',$request->email)->exists())
        {

            return redirect(url('login'))
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'Mail Sent! Check your Inbox to reset password',
                        'type' => 'success',
                    ]
                ]);
        }

        $data['token'] = str_random(80);
        User::where('email',$request->email)->update([
            'token'=>$data['token']
        ]);
        $data['user']=User::where('email',$request->email)->first();
        try {
            Mail::to($request->email)->send(new PasswordResetLinkMaill($data));

        }
        catch (\Exception $e)
        {

            return redirect(url('login'))
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'Something Going Wrong',
                        'type' => 'error',
                    ]
                ]);
        }
        return redirect(url('login'))
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Mail Sent! Check your Inbox to reset password',
                    'type' => 'success',
                ]
            ]);
    }
    public function verify_email(Request $request,$token)
    {
        return view('auth.passwords.reset',['token'=>$token]);
    }
    public function update_password(Request $request)
    {

        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        $data['user']=User::where('token',$request->token)->first();

        Notification::send(User::where('token',$request->token)->get(),new passwordChangedNotification($data));
        User::where('token',$request->token)->update([
            'token'=>'12121h1h2h1h20',
            'password'=>bcrypt($request->password)
        ]);
        return redirect(url('login'));

    }
}
