<?php

namespace App\Http\Controllers;

use App\Models\eft_sent_bill;
use App\Models\efttransaction;
use App\Models\PaidBill;
use App\Notifications\billProcessCompleted;
use App\Notifications\eftAddFundsTransactionCompleted;
use App\Notifications\sendFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use League\Csv\Reader;

class bmoController extends Controller
{
    public function index()
    {

        return view('bmo.index');
    }
    public function UploadPaidBillsFile(Request $request)
    {

        $file = $request->file('file');

        $csv = Reader::createFromFileObject($file->openFile());

        if ($csv->count()<2)
        {
            return back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'There is no record to processed in the file',
                        'type' => 'error',
                    ]
                ]);
        }

        foreach ($csv as $index => $row) {

                if ($index==0)
                {
//                dd($row);
                }
                else
                {




                    $record=eft_sent_bill::where('record_id',sprintf("%019s",$row[9]))->where('status','sent')->whereHas('file',function ($q) use ($row){
                        $q->where('file_sequence',sprintf("%04s",$row[0]));
                        $q->where('type','C');
                    })->first();


                    if ($record) {

                        if ($row[12] == "Confirmed" || $row[12] == "confirmed") {

                            $tempData['bill'] = $record->bill;
                            $tempData['user'] = $record->bill->user;
                            $tempData['payee'] = $record->bill->payee;
//                            $record->status = "Processed";
                            $record->new_status = $row[12];
                            $record->status = "Processed";
                            $record->reason ="";
                            $record->save();
                           $piadBill=PaidBill::find($record->paid_bill_id);
                            $piadBill->is_sent_to_pay="Processed";
                            $piadBill->save();
                            Notification::route('mail', $record->bill->user->email)->notify(new billProcessCompleted($tempData));

                        } else {
                            $piadBill=PaidBill::find($record->paid_bill_id);
                            $piadBill->is_sent_to_pay="Not-Paid";
                            $piadBill->save();
                            $record->new_status = $row['12'];
                            $record->reason = $row['13'];
                            $record->save();
                        }
                    }


                }


        }
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'File has been processed',
                    'type' => 'success',
                ]
            ]);
    }
    public function UploadAddFundsFile(Request $request)
    {

        $file = $request->file('file');

        $csv = Reader::createFromFileObject($file->openFile());
        if ($csv->count()<2)
        {
            return back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'There is no record to processed in the file',
                        'type' => 'error',
                    ]
                ]);
        }

        foreach ($csv as $index => $row) {

            if ($index==0)
            {
//                dd($row);
            }
            else
            {
            $yourfilename=$file->getClientOriginalName();
           $myfilename="D".sprintf("%04s",$row[0]);



                $record=efttransaction::where('record_id',sprintf("%019s",$row[9]))->where('status','sent')->whereHas('file',function ($q) use ($row){
                    $q->where('file_sequence',sprintf("%04s",$row[0]));
                    $q->where('type','D');
                })->first();


                if ($record) {

                    if ($row[12] == "Confirmed" || $row[12] == "confirmed") {

                        $tempData['user'] = $record->user;
//                        Notification::route('mail', $record->bill->user->email)->notify(new eftAddFundsTransactionCompleted($tempData));
//                            $record->status = "Processed";
                        $record->new_status = $row[12];
                        $record->status = "Processed";
                        $record->reason ="";
                        $record->save();
                        add_balance_to_wallet($record->user->id,$record->actual_amount);
                    } else {
                        $record->new_status = $row['12'];
                        $record->reason = $row['13'];
                        $record->save();
                    }
                }


            }


        }
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'File has been processed',
                    'type' => 'success',
                ]
            ]);
    }
    public function all_transactions(Request $request)
    {
        $data['wallet_transactions']=efttransaction::all();
        $data['bills_transactions']=eft_sent_bill::all();
        return view('bmo.all-transactions',$data);
    }
}
