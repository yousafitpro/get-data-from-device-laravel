<?php

namespace App\Http\Controllers;

use App\Models\PaidBill;
use App\Models\telpay_file;
use App\Models\telpaysentBill;
use App\Models\User;
use App\Notifications\BillsSentToTelpay;
use App\Notifications\noBillsFoundToProcess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

class talpayController extends Controller
{
    public function ServicesList(Request $request)
    {
        $res=Http::accept('application/json')->get('https://secure2.telpay.ca/cgi-bin/payment_services?ObtainBillerList&Userid=005350&Password=9vbg748N&PartOfBillerName='.$request->company.'&BillerType=*');

        if ($res->status()=='200')
        {
            $xmlObject = simplexml_load_string($res->body());

            $json = json_encode($xmlObject);
            $phpDataArray = json_decode($json, true);
            $data='';

            if ($phpDataArray['Result']=='1')
            {
///asas
                if ($request->has('type') && $request->type=='json')
                {

                    return response()->json(['list'=>$phpDataArray['ArrayOfClsBiller']['ClsBiller']]);
                }

                return view('ajax-components.telpayListItem',['list'=>$phpDataArray['ArrayOfClsBiller']['ClsBiller']]);

            }
            else
            {
                if (Request::capture()->expectsJson())
                {
                    return response()->json(['list'=>[]]);
                }
                return view('ajax-components.telpayListItem',['list'=>[]]);


            }

        }


    }

    public function index()
    {

        return view('telpay.index');
    }
    public function all_transactions(Request $request)
    {
        $data['list']=telpaysentBill::all();
        return view('telpay.all-transactions',$data);
    }
    function validateAccountNumber(Request $request)
    {
        $res=Http::get('https://secure2.telpay.ca/cgi-bin/payment_services?ValidateBillAccountNumber&Userid=005350&Password=9vbg748N&BillerCode='.$request->BillerCode.'&BillAccountNumber='.$request->BillAccountNumber);


        $xmlObject = simplexml_load_string($res->body());

        $json = json_encode($xmlObject);
        $parsedData = json_decode($json, true);

        if ($res->status()=='200')
        {

            return response()->json($parsedData);

        }
    }
//   public static function generateFile($fileId)
//    {
//        $bills=telpaysentBill::where('telpay_file_id',$fileId)->get();
//        if ($bills->count()<=0)
//        {
//            $tData['type']="Telpay";
//            return redirect(url('telpay/index'))
//                ->with([
//                    'toast' => [
//                        'heading' => 'Message',
//                        'message' => 'There is no bill to process.',
//                        'type' => 'success',
//                    ]
//                ]);
//        }
//
//
//
//
//       $filename="OI005350";
//       $full_filename='telpayfiles/'.$filename.'.'."tst";
//       $BatchFile = fopen($full_filename,'wb') or die("Unable to open file!");
//       $header_date=Carbon::parse(time_now())->format('Ymdhi');
////        $txt = "PC00000005350  2801884F001 B430       ".$header_date." asas                                                                         \n";
//
////        $header_date=str_pad($header_date,59," ",STR_PAD_RIGHT);
//       $txt="PC00000005350  2801884F".$file_sequence_number." B430        ".$header_date."                                                                                                                                                     "."\r\n";
//       fwrite($BatchFile, $txt);
////        $txt = "\n";asdadasd
////        fwrite($BatchFile, $txt);
//       $tota_Amount=0;
//
//       foreach($paidBills as $index => $pb)
//       {
//
//           $amount=sprintf("%010s",number_format($pb->amount,2) );
//           $amount=str_replace('.','',$amount);
//           $bill_id=sprintf("%04s", $index+1);
//           $system_bill_id=sprintf("%010s", $pb->id);
//           $d=substr($pb->payee->account_number,0,30);
//           echo "Acount :".$pb->payee->account_number."<br>";
//           echo "Modified Account: ".self::convertAccountNumberToDigitsOnly($pb->payee->account_number)."<br>";
//           $total_of_firs_12=$total_of_firs_12+self::convertAccountNumberToDigitsOnly($pb->payee->account_number);
//           $pb->payee->account_number= str_pad($pb->payee->account_number,30," ",STR_PAD_RIGHT);
//           $pb->user->name= str_pad($pb->user->name,30," ",STR_PAD_RIGHT);
////           dd($pb->payee->account_number);
//           $pb->payee->account_number=strtoupper($pb->payee->account_number);
//           $spaces_after_name="";
//           $tota_Amount=$tota_Amount+$amount;
//
//           $spaces_after_name=str_pad($spaces_after_name,99," ",STR_PAD_RIGHT);
//           $nText="PC3".$bill_id."         ".$pb->payee->code." ".$amount." 00000ACT".$pb->payee->account_number.$pb->user->name.$spaces_after_name."\r\n";
////            $nText = "PC00000005350    2801884f001    B430    ".$header_date."\n";
//           fwrite($BatchFile, $nText);
//
////          if (!telpaysentBill::where(['record_id'=>"PC3".$bill_id,'paid_bill_id'=>$pb->id])->exists())
////          {
//           telpaysentBill::create([
//               'record_id'=>"PC3".$bill_id,
//               'paid_bill_id'=>$pb->id,
//               'telpay_file_id'=>$file->id
//           ]);
////            $pb->is_sent_to_pay='sent';
////            $pb->save();
////          }
//       }
////            dd($total_of_firs_12);
//       echo "Sum of first 12 Digits of paid Bills :".$total_of_firs_12."<br>";
//       $total_paid_bills_Count_withHeader=sprintf("%05s",$total_paid_bills+2);
//       $total_paid_bills=sprintf("%04s",$total_paid_bills );
//       $tota_Amount=sprintf("%013s",$tota_Amount);
//       // adding total of first 12 digit of all bills to total amount
//       $total_of_firs_12=$total_of_firs_12+intval(substr($tota_Amount,1,13));
//       $tl=strlen($total_of_firs_12);
//       $tl_2=$tl-12;
//
//       $trailer_hash=substr($total_of_firs_12,$tl_2,$tl);
//       $spaces_after_amount=str_pad('',93,"0",STR_PAD_RIGHT);
////        $trailer_hash=str_pad('',12,"#",STR_PAD_RIGHT);
//       $blank_after_trailer=str_pad('',25," ",STR_PAD_RIGHT);
//
//       echo "total_paid_bills_Count_withHeader : ".$total_paid_bills_Count_withHeader."<br>";
//       echo "total_paid_bills : ".$total_paid_bills."<br>";
//       echo "Total Amount: ".$tota_Amount."<br>";
//       echo "Hash : ".$trailer_hash."<br>";
//
////        echo "Total Number Of Bills :".$trailer_hash."<br>"  asas;
//       $txt="PCZZZZZ".$total_paid_bills_Count_withHeader.$total_paid_bills.$tota_Amount.$spaces_after_amount.$trailer_hash.$blank_after_trailer."Y\r\n";
//       fwrite($BatchFile, $txt);
//       // end of Writing Batch File
//
//
//       // start of Writing Report
//       $tempData['totalAmount']=$paidBills->sum('amount');
//       $tempData['number_of_bills']=$paidBills->count();
//       $total_bills=$paidBills->count();
//       $reportFile = fopen('telpayfiles/report.txt', "w") or die("Unable to open file!");
//       $txt = "Total Number Of Bills :".$total_bills."\n";
//
//       fwrite($reportFile, $txt);
//       $txt = "Total Amount :".$tempData['totalAmount']."$\n";
//       fwrite($reportFile, $txt);
//       // end of Writing Report
//
//
//       $files = [
//           public_path($full_filename),
//           public_path($full_filename),
//           public_path("telpayfiles/report.txt"),
//       ];
//       //sasa
//       $tempData['files']=$files;
//
//       $tempData['filenumber']=$full_filename;
//
////       Notification::send(User::whereIn('id',app_controllers())->get(),new BillsSentToTelpay($tempData));
//
//
//    }
}
