<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AlertController extends Controller
{
   public static function create($data)
   {
       Alert::create($data);

   }
   public static function all()
   {
       $data['list']=Alert::all();
   }
   public static function myAlerts(Request $request)
   {
//fdgfdgsdsd sasasas

       $q=Alert::where('id','!=','asdas');
       $data['unread_count']=alert_unread_count();
       // status Check
       if ($request->has('status') && $request->status!="all" )
       {
           $q=$q->where('status',$request->status);
           if (!Request::capture()->expectsJson()) {
               Session::put('myalert_status', $request->status);
           }

       }
       else
       {
           if (!Request::capture()->expectsJson()) {
               Session::put('myalert_status', 'all');
           }
       }
       // date check

       if ($request->has('date') and $request->date!=null)
       {
           $q=$q->whereDate('created_at',$request->date);
           if (!Request::capture()->expectsJson()) {
               Session::put('myalert_date', $request->date);
           }

       }
       else{
           if (!Request::capture()->expectsJson())
           {
               Session::put('myalert_date',null);
           }

       }
       $data['list']=$q->where([
           "receiver"=>auth()->user()->id
       ])->orderBy('id', 'DESC')->get();


               Alert::where([
            "receiver"=>auth()->user()->id,
            'status'=>'created'
        ])->update([
            'status'=>'viewed'
        ]);


       if (Request::capture()->expectsJson())
       {
           return response()->json($data);
       }

       return view('alert.myAlerts')->with($data);

   }
   public function opened($id)
   {
       Alert::where('id',$id)->update(['status'=>'opened']);
       if (Request::capture()->expectsJson())
       {
           return response()->json(['message'=>"Done"]);
       }
       return back();

   }
}
