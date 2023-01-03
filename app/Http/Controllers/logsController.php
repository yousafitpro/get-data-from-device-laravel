<?php

namespace App\Http\Controllers;

use App\Models\eft_debit_file;
use App\Models\eft_file;
use App\Models\eft_sent_bill;
use App\Models\efttransaction;
use App\Models\packageTransaction;
use App\Models\PaidBill;
use App\Models\telpay_file;
use App\Models\telpaysentBill;
use Illuminate\Http\Request;

class logsController extends Controller
{
  public function transactions(Request $request)
  {
      $q=packageTransaction::where('id','!=','as12')->with("user");
      if ($request->has('status'))
      {
          $q->where('status',$request->status);
      }
      if ($request->has('type'))
      {
          $q->where('type',$request->type);
      }
      $data['transactions']=$q->get();

      return view('logs.transactions',$data);
  }
    public function bills(Request $request)
    {
        $q=PaidBill::where('id','!=','as12')->with(["user",'payee']);
        if ($request->has('status'))
        {
            $q->where('status',$request->status);
        }
        if ($request->has('bill_status'))
        {
            $q->where('is_sent_to_pay',$request->bill_status);
        }
        $data['bills']=$q->get();

        return view('logs.bills',$data);
    }
    public function telpay_files(Request $request)
    {
        $data['files']=telpay_file::all();
        return view('logs.telpayfiles',$data);
    }
    public function today_telpay_file()
    {
        $data['bills']=PaidBill::where('status','amount-received')->where('is_sent_to_pay','Pending')->where('type','service')->with('payee')->take(9999)->get();
        return view('logs.todaytelpayfile',$data);
    }
    public function telpay_file_bills(Request $request,$id)
    {
        $data['bills']=telpaysentBill::where('telpay_file_id',$id)->get();
        return view('logs.telpayfilebills',$data);
    }
    public function eft_file_bills(Request $request,$id)
    {




             if ($request->type=="credit_bills")
             {
                 $data['bills']=eft_sent_bill::where('eft_file_id',$id)->get();
             }
             else
             {
                 $data['bills']=efttransaction::where('eft_file_id',$id)->get();
             }


        return view('logs.eftfilebills',$data);
    }
    public function eft_files(Request $request)
    {
        $data['credit_bill_files']=eft_file::all();
        $data['debit_wallet_files']=eft_debit_file::all();
        return view('logs.eftfiles',$data);
    }
    public function today_eft_file()
    {
        $data['bills']=PaidBill::where('status','amount-received')->where('is_sent_to_pay','Pending')->where('type','self-added')->with('payee')->take(9999)->get();
        $data['wallet_bills']=efttransaction::where('deleted_at',null)->where('status','Pending')->take(9999)->get();

        return view('logs.todayeftfile',$data);
    }
}
