<?php

namespace App\Http\Controllers;

use App\Models\contact;
use App\Models\etransfer;
use App\Models\etransfer_transaction;
use App\Models\sendandrequestfund;
use App\Models\User;
use Illuminate\Http\Request;

class EtransferController extends Controller
{
    public function transfer()
    {

        $data['transactions']=etransfer_transaction::where('user_id',auth()->user()->id)->get();
        foreach ($data['transactions'] as $t)
        {
            $t->user=User::find($t->user_id);
        }
        if (Request::capture()->expectsJson())
        {
            return  response()->json($data);
        }
        return view('banking.transfer',$data);
    }
    public function e_transfer_detail()
    {
        $data['users']=contact::where(['deleted_at'=>null,'creator_id'=>auth()->user()->id])->get();
         $data['detail']=etransfer::where('user_id',auth()->user()->id)->first();
         $data['balance']=etransfer_transaction::where('user_id',auth()->user()->id)->get()->sum('amount');
        $data['transactions']=etransfer_transaction::where('user_id',auth()->user()->id)->get();
        foreach ($data['transactions'] as $t)
        {
            $t->user=User::find($t->user_id);
        }
        if (Request::capture()->expectsJson())
        {
            return  response()->json($data);
        }
        $data['history']=sendandrequestfund::where(['user_id'=>auth()->id(),'direction'=>'send'])->get();
        return view('banking.etransfer',$data);
    }
    public function sendAndRequestFund()
    {
        return view('banking.sendandrequestfund');
    }
    public function e_transfer_all_transactions(Request $request)
    {

        $data['transactions']=etransfer_transaction::all();
        foreach ($data['transactions'] as $t)
        {
            $t->user=User::find($t->user_id);
        }
        return view('banking.all_transactions',$data);
    }
    public function e_transfer_update_transaction(Request $request,$id)
    {
        $data=$request->except(['_token']);
        etransfer_transaction::where('id',$id)->update($data);
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Transaction Updated Successfully',
                    'type' => 'success',
                ]
            ]);
    }
    public function e_transfer_add_new_transaction(Request $request)
    {
        $data=$request->except(['_token']);
        $data['operator_id']=auth()->user()->id;
        etransfer_transaction::create($data);
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Transaction Add Successfully',
                    'type' => 'success',
                ]
            ]);
    }
}
