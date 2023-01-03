<?php

namespace App\Http\Controllers;
use Twilio\Rest\Client;
use App\Helper\myHelper;
use App\Models\etransfer;
use App\Models\userNote;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TwilioController extends Controller
{
 public static function sendMessage($receiver_number,$message,$data)
 {

     $account_sid =config('myconfig.Twilio.sid');
     $twilio_number = config('myconfig.Twilio.from');
      $token=config('myconfig.Twilio.token');
     $client = new Client($account_sid, $token);
     $client->messages->create(
     // Where to send a text message (your cell phone?)
         $receiver_number,
         array(
             'from' => $twilio_number,
             'body' => $message
         ));
 }
    public static function sendMessageToUser($user,$message)
    {
        $set=UserSetting::where('user_id',$user->id)->first();
        if ($set->is_phone_verified='true')
        {

            self::sendMessage($user->phone,$message,[]);
        }

    }
}
