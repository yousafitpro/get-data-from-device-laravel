<?php

namespace App\Http\Controllers;

use App\Mail\BillFailMail;
use App\Models\supportquary;
use App\Models\User;
use App\Notifications\sendPhoneVerificationCode;
use App\Notifications\sendqueryreply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class SupportquaryController extends Controller
{
    public function create_query_view()
    {
        return view('support.create');
    }
    public function reply_query_view($id)
    {
        $data['item']=supportquary::find($id);
        return view('support.reply',$data);
    }
    public function all_queries()
    {

        $data['list']=supportquary::all();
        foreach ($data['list'] as $u)
        {
            $u->user=User::find($u->user_id);
        }
        return view('support.all',$data);
    }
    public function create_query(Request $request)
    {

        $data=$request->except(['_token']);
        $data['user_id']=auth()->user()->id;
        supportquary::create($data);
        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>"Query Successfully Sent to Support Team"]);
        }
        return redirect(url('/dashboard'))->with([
        'toast' => [
            'heading' => 'Message',
            'message' => 'Query Successfully sent to Support Team',
            'type' => 'success',
        ]
    ]);
    }
    public function reply_query(Request $request,$id)
    {
        $query=supportquary::find($id);
        $users=User::where('id',$query->user_id)->get();
        $data['user']=User::where('id',$query->user_id)->first();
//        Notification::send($users,new sendqueryreply($data));
        $data['myreply']=$request->myreply;
        $email =new \App\Mail\sendqueryreply($data);
        Mail::to($data['user']->email)->send($email);
        $query->updated_at=null;
        $query->save();
        return redirect(url('/support/all-queries'))->with([
            'toast' => [
                'heading' => 'Message',
                'message' => 'Reply Successfully Sent to User',
                'type' => 'success',
            ]
        ]);
    }
}
