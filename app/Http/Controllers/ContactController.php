<?php

namespace App\Http\Controllers;

use App\Models\contact;
use App\Models\User;
use App\Notifications\billPaymentStartedUsingWallet;
use App\Notifications\userNotInZpayd;
use App\Notifications\userNotInZpaydContact;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    public function add(Request $request)
    {
//            $request->validate([
//               'transit_number'=>'required|integer|digits:5',
//                'account_number'=>'required|integer|digits:7',
//                'institution_number'=>'required|integer|digits:3',
//            ]);
        $data=$request->except(['_token','token']);
        $data['creator_id']=auth()->user()->id;
        if (!User::where('email',$request->username)->exists())
        {
            $user=new User();
            $user->name=$request->full_name;
            $user->email=$request->username;
            $user->password='1231';
            $user->save();
            $tempData['user']=$user;
            $tempData['sender']=auth()->user();

            Notification::send(User::where('id',$user->id)->get(),new userNotInZpaydContact($tempData));

        }
        if (contact::where('creator_id',$data['creator_id'])->where('username',$request->username)->exists())
        {


            $data['deleted_at']=null;
            if (User::where('email',$request->username)->exists())
            {
                $nuser2=User::where('email',$request->username)->first();
                AlertController::create([
                    'message'=>$nuser2->name." added you in his/her network you can also add them back .",
                    'title'=>"My Network!",
                    'type'=>'network',
                    'receiver'=>$nuser2->id,
                    'sender'=>auth()->user()->id
                ]);


            }

            contact::where('creator_id',$data['creator_id'])->where('username',$request->username)->update($data);
            if (Request::capture()->expectsJson())
            {
                return response()->json(['message'=>"Contact Successfully Added"]);
            }
            return back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'Contact Successfully Updated',
                        'type' => 'success',
                    ]
                ]);
        }
        else
        {
           $nn= contact::create($data);
           $nuser=User::where('email',$nn->username)->first();
            AlertController::create([
                'message'=>$nuser->name." added you in his/her network you can also add them back .",
                'title'=>"My Network!",
                'type'=>'network',
                'receiver'=>$nuser->id,
                'sender'=>auth()->user()->id
            ]);

            if (Request::capture()->expectsJson())
            {
                return response()->json(['message'=>"Contact Successfully Added"]);
            }
            return back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'Contact Successfully Added',
                        'type' => 'success',
                    ]
                ]);
        }

    }
    public static function remainingUsers()
    {
        $users=User::where("users.id",'!=',auth()->user()->id)->where('users.id','!=',who_is_admin())
            ->get();

    foreach ($users as $u)
    {
        $u->is_already=false;
        if (contact::where('username',$u->email)->where('creator_id',auth()->user()->id)->where('deleted_at',null)->exists())
        {
            $u->is_already=true;
        }
    }

       if(Request::capture()->expectsJson())
       {
           return response()->json(['list'=>$users]);
       }
       return $users;
    }
    public function update(Request $request,$id)
    {

        $data=$request->except(['_token','token']);
        contact::where('id',$id)->update($data);
        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>"Contact Successfully Updated"]);
        }
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Contact Successfully Updated',
                    'type' => 'success',
                ]
            ]);
    }
    public function list()
    {
        $data['list']=contact::where('creator_id',auth()->user()->id)->where('deleted_at',null)->get();
          if (Request::capture()->expectsJson())
          {
              return response()->json($data);
          }
          $data['users']=self::remainingUsers();
        return view('contact.list',$data);
    }
    public function remove($id)
    {

        contact::where('id',$id)->update(['deleted_at'=>Carbon::now()->toDateString()]);
        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>"Contact Successfully Removed"]);
        }
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Contact Successfully Deleted',
                    'type' => 'success',
                ]
            ]);
    }
}
