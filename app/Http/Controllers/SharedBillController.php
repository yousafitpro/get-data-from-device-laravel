<?php

namespace App\Http\Controllers;

use App\Models\contact;
use App\Models\mypayee;
use App\Models\sharedBill;
use App\Models\sharedBillMember;
use App\Models\User;
use App\Notifications\billPaymentStartedUsingWallet;
use App\Notifications\userNotInZpayd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class SharedBillController extends Controller
{
    public function add(Request $request)
    {

        $request->validate([
            'amount'=>'required|integer|max:'.ENVController::$maxBillAmountLimit,
            'due_date'=>'required'
        ]);
        $data=$request->except(['token','_token']);

        $data['user_id']=auth()->user()->id;
        $bill=sharedBill::create($data);

        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>'Shared Bill has Been Created','id'=>$bill->id]);
        }
      return redirect(route('sharedBill.edit',$bill->id));

    }
    public function edit($id)
    {
        $data['bill']=sharedBill::find($id);
        $data['contacts']=contact::where('creator_id',auth()->user()->id)->where('deleted_at',null)->get();
        $data['bill']->payee=mypayee::where('id',sharedBill::find($id)->mypayee_id)->first();
        $data['payees']=sharedBillMember::where('deleted_at',null)->where('user_id',auth()->user()->id)->where('shared_bill_id',$id)->get();
        foreach ($data['payees'] as $item)
        {
            $item->contact=contact::find($item->contact_id);
        }
        if (Request::capture()->expectsJson())
        {
            return response()->json($data);
        }

        return view('bills.share-create',$data);
    }
    public function update(Request $request,$id)
    {
        $data=$request->except(['token','_token']);
        sharedBill::where('id',$id)->update($data);
        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>'Shared Bill has Been Updated']);

        }
        return redirect()->back()->with([
            'toast' => [
                'heading' => 'Message',
                'message' => 'Shared Bill has Been Updated',
                'type' => 'success',
            ]
        ]);
    }
    public function addMember(Request $request)
    {
        $data=$request->except(['token','_token']);
        $data['user_id']=auth()->user()->id;
        $contact=contact::find($request->contact_id);
        if (!User::where('email',$contact->username)->exists())
        {
            $user=new User();
            $user->name=$contact->full_name;
            $user->email=$contact->username;
            $user->password='1231';
            $user->save();
            $tempData['user']=$user;
            $tempData['sender']=auth()->user();
            Notification::send(User::where('id',$user->id)->get(),new userNotInZpayd($tempData));

        }
        if (sharedBillMember::where(['contact_id'=>$request->contact_id,'shared_bill_id'=>$request->shared_bill_id,'user_id'=>auth()->user()->id])->exists())
        {
            sharedBillMember::where(['contact_id'=>$request->contact_id,'shared_bill_id'=>$request->shared_bill_id,'user_id'=>auth()->user()->id])->update($data);

            if (Request::capture()->expectsJson())
            {
                return response()->json(['message'=>'Member Info Updated']);
            }
        }
        else
        {
            sharedBillMember::create($data);
            if (Request::capture()->expectsJson())
            {
                return response()->json(['message'=>'New member added']);
            }

        }


    }
    public function sharedBills()
    {
        $data['list']=sharedBill::where(['deleted_at'=>null,'status'=>'Pre-Complete'])->get();
        foreach ($data['list'] as $i)
        {
            $i->payee=mypayee::find($i->mypayee_id);
            $i->count=sharedBillMember::where('shared_bill_id',$i->id)->get()->count();
            $i->members=sharedBillMember::where('shared_bill_id',$i->id)->get();
            foreach ($i->members as $m)
            {
                $m->contact=contact::find($m->contact_id);
            }
        }
        return response()->json($data);
    }
    public function removeMember($id)
    {
        sharedBillMember::where(['id'=>$id,'user_id'=>auth()->user()->id])->delete();

        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>'Operation Successful']);
        }
        return redirect()->back()->with([
            'toast' => [
                'heading' => 'Message',
                'message' => 'Member removed successfully',
                'type' => 'success',
            ]
        ]);
    }
    public function complete($id)
    {
        sharedBill::where('id',$id)->update(['status'=>'Pre-Complete']);
        $bill=sharedBill::find($id);
//
//        $members=sharedBillMember::where('shared_bill_id',$id)->get();
//
//        foreach ($members as $m)
//        {
//            if (User::where('email',$m->contact->username)->exists())
//            {
//                $tempData['user']=User::where('email',$m->contact->username)->first();
//                Notification::send(User::where('email',$m->contact->username)->get(),new userNotInZpayd($tempData));
//            }
//        }


           if (sharedBillMember::where('shared_bill_id',$id)->get()->count()==0)
           {
               return redirect()->back()->with([
                   'toast' => [
                       'heading' => 'Message',
                       'message' => 'Please Add at least one member',
                       'type' => 'error',
                   ]
               ]);
           }
           $total=sharedBillMember::where('shared_bill_id',$id)->sum('amount');


        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>'Transaction has been Started ']);
        }
        if ($total>$bill->amount)
        {
            return redirect()->back()->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Members Amount Must be less than Total Bill Amount',
                    'type' => 'error',
                ]
            ]);
        }
        return redirect(url('my-blls/tabs?tab=Payees'))->with([
            'toast' => [
                'heading' => 'Message',
                'message' => 'Transaction has been Started',
                'type' => 'success',
            ]
        ]);
    }
    public function memberConfirm(Request $request,$id)
    {

        $request->validate([
            'amount'=>'required:integer'
        ]);
        $member= sharedBillMember::where('id',$id)->first();
        $bill=sharedBill::where('id',$member->shared_bill_id)->first();
        if ($request->amount>$member->amount)
        {
            return redirect()->back()->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Sorry you cannot pay more than assigned bill amount',
                    'type' => 'error',
                ]
            ]);
        }
       sharedBillMember::where('id',$id)->update([
            'status'=>'Confirmed',
            'amount'=>$request->amount
        ]);
        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>'Transaction Successfully Started']);
        }
        return redirect()->back()->with([
            'toast' => [
                'heading' => 'Message',
                'message' => 'Transaction Successfully Started',
                'type' => 'success',
            ]
        ]);
    }
    public function shared_bill_members($id)
    {
        $data['list']=sharedBillMember::where('shared_bill_id',$id)->get();
        foreach ($data['list'] as $c)
        {
            $c->contact=contact::find($c->contact_id);
        }
        return view('mybills.billmembers',$data);
    }
}
