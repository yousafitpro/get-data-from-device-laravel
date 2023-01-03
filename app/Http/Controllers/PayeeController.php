<?php

namespace App\Http\Controllers;

use App\Helper\myHelper;
use App\Models\mypayee;
use App\Models\payee;
use App\Models\suggestedPayeeListItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayeeController extends Controller
{
    //asdasd
    public function suggested_list()
    {
        //asdasd
        $data['list']=suggestedPayeeListItem::all();

        return view('payee.suggested_list',$data);
    }
    function save_suggested_payee(Request $request)
    {
        suggestedPayeeListItem::create($request->all());
        return response()->json(['message'=>"Payee added"]);
    }
   public function add(Request $request)
   {


       $payee=null;

       $data=$request->except('_token','token');
       $data['user_id']=auth()->user()->id;
         if (payee::where(['account_number'=>$request->account_number,'user_id'=>auth()->user()->id])->exists())
         {


             payee::where('account_number',$request->account_number)->where('user_id',auth()->user()->id)->update($data);
             $payee=payee::where('account_number',$request->account_number)->where('user_id',auth()->user()->id)->first();

         }
         else
         {

             $payee=payee::create($data);
             $nickname=null;
             if ($request->type=="Personal")
             {
                 $nickname=$request->first_name.' '.$request->last_name;

             }
             else
             {

                 $nickname=$request->company_name;

             }

             myHelper::add_my_payee_as_self(auth()->user()->id,$nickname,$payee->id);

         }


         if (Request::capture()->expectsJson())
         {
             return response()->json(['message'=>'Payee Successfully Added']);
         }
       return redirect(url('my-blls/tabs?tab=Payees'))
           ->with([
               'toast' => [
                   'heading' => 'Message',
                   'message' => 'Payee Successfully Added',
                   'type' => 'error',
               ]
           ]);

   }
   public function add_my_payee(Request $request)
   {
       myHelper::add_my_payee(auth()->user()->id,$request->code,$request->name,$request->account_number,$request->nickname);
       if (Request::capture()->expectsJson())
       {
           return response()->json(['message'=>'Payee Successfully Added']);
       }
       return redirect(url('my-blls/tabs?tab=Payees'))
           ->with([
               'toast' => [
                   'heading' => 'Message',
                   'message' => 'Payee Successfully Added',
                   'type' => 'error',
               ]
           ]);
   }
    public function remove_my_payee($id)
    {
       mypayee::where('id',$id)->update([
           'deleted_at'=>Carbon::now()
       ]);
        if (Request::capture()->expectsJson())
        {
            return  response()->json(['message'=>'Payee Successfully Removed']);
        }
        return redirect(url('my-blls/tabs?tab=Payees'))
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Payee Successfully Removed',
                    'type' => 'success',
                ]
            ]);
    }
    public function providers()
    {
        if (Request::capture()->expectsJson())
        {
            $list=my_payees(who_is_admin());
            return response()->json(['list'=>$list]);
        }
    }
    public function mypayees()
    {
        if (Request::capture()->expectsJson())
        {
            $list=my_own_payees(auth()->user()->id);

            return response()->json(['list'=>$list]);
        }
    }
}
