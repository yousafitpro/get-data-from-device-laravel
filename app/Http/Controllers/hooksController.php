<?php

namespace App\Http\Controllers;

use App\Models\eft_debit_file;
use App\Models\efttransaction;
use App\Notifications\noBillsFoundToProcess;
use App\Notifications\WalletTransactionsSentToEft;
use Excel;
use App\Excel\Exports\EFTExcelExport;
use App\Models\eft_file;
use App\Models\eft_sent_bill;
use App\Models\PaidBill;
use App\Models\payee;
use App\Models\telpay_file;
use App\Models\telpaysentBill;
use App\Models\User;
use App\Notifications\billPaymentStartedUsingWallet;
use App\Notifications\BillsSentToEft;
use App\Notifications\BillsSentToTelpay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class hooksController extends Controller
{
    public function loginAndRedirect(Request $request)
    {

        $request->validate([
            'redirectUrl'=>'required',
            'username'=>'required',
            'password'=>'required'
        ]);
        $credentials = [
            'email' => $request['username'],
            'password' => $request['password'],
        ];

        if (Auth::attempt($credentials)) {
            Session::put('login_2FA',true);
            Session::put('login_email_2FA',true);
            Session::put('from_app',true);
            return redirect($request->redirectUrl.'?is_from_login='.$request->is_from_login);
        }
   abort(401);
        return redirect($request->redirectUrl);
    }
    public function sendBillsToTelpay()
    {
     self::createtelpayFile();
     self::createEftFile();
     self::createDebitEftFile();
    }
    public function createEftFile()
    {


        $paidBills=PaidBill::where('status','amount-received')->where('is_sent_to_pay','Pending')->where('type','self-added')->with('payee')->take(9999)->get();

        if ($paidBills->count()<=0)
        {
            $tData['type']="EFT-Bills";
            Notification::send(User::whereIn('id',app_controllers())->get(),new noBillsFoundToProcess($tData));
            return redirect(url('bmo/index'))
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'There is no bill to process.',
                        'type' => 'success',
                    ]
                ]);
        }
        $file_sequence_number='0001';
        if (eft_file::where('id','!=','121e34')->exists())
        {
            $records=eft_file::where('id','!=','121e34')->latest()->first();

            $file_sequence_number=$records->file_sequence;
            $file_sequence_number=intval($file_sequence_number)+1;
        }

        $file_sequence_number=sprintf("%04s", $file_sequence_number);
        $file=eft_file::create([
            'file_sequence'=>$file_sequence_number,
            'type'=>"C",
            'status'=>"Sent"
        ]);


        $total_of_firs_12=0;
        $total_paid_bills=$paidBills->count();


        // start of Writing Batch File
        $fileNumber=$file_sequence_number;
        $filename="C".$file_sequence_number;
        $full_filename='eftfiles/'.$filename;
        $BatchFile = fopen($full_filename.".txt", "w") or die("Unable to open file!");

        $header_date=Carbon::parse(time_now())->addDay();

        $blank_after_data_center=str_pad('',54," ",STR_PAD_RIGHT);
        //File Header - Record Type A (80 Character)

        //Batch Header - Record Type X (80 Character)
        $header_date_year=Carbon::parse($header_date)->format('y');
        $header_date_day=Carbon::parse($header_date)->format('z');
        $txt="ACRFLUGATOR".$file_sequence_number.'0'.$header_date_year.$header_date_day.'00120'.$blank_after_data_center."\r";
        fwrite($BatchFile, $txt);
        $shortName="zPAYD";
        $shortName=str_pad($shortName,15," ",STR_PAD_RIGHT);

        $longName="Flugator Inc.";
        $longName=str_pad($longName,30," ",STR_PAD_RIGHT);
        $payable_date=Carbon::parse(time_now())->addDay();
        $payable_date_year=$payable_date->format('y');
        $payable_date_day=$payable_date->format('z');
        $return_account_number="1992680";
        $return_account_number=str_pad($return_account_number,12," ",STR_PAD_RIGHT);
        $txt="XC4300".$payable_date_year.$payable_date_day.$shortName.$longName."000128022".$return_account_number."   \r";
        fwrite($BatchFile, $txt);
        $total_amount=0;
        foreach($paidBills as $index => $pb)
        {


            $payee=payee::find($pb->payee->payee_id);
            $amount=$pb->amount;
            $bill_id=sprintf("%04s", $index+1);
            if($payee->type=="Personal")
            {
                $bill_name=$payee->first_name." ".$payee->last_name;
            }
            else
            {
                $bill_name=$payee->company_name;
            }

            $amount=sprintf("%010s",number_format($pb->amount,2) );
            $amount=str_replace('.','',$amount);
            $amount=sprintf("%010s",$amount);
            $total_amount=$total_amount+$amount;
            $account_number=$longstr = str_pad($payee->account_number, 12);
            $name=str_pad(Str::limit($bill_name,29,''),29);
            $ref=Str::limit(sprintf("%019s",$index ),29,'');
            //Detail Record - 'C' or 'D' (80 Character)
            $txt="C".$amount."0".$payee->institution_number.$payee->transit_number.$account_number.$name.$ref."\r";
            fwrite($BatchFile, $txt);
            eft_sent_bill::create([
                'record_id'=>$ref,
                'paid_bill_id'=>$pb->id,
                'source'=>'bill',
                'eft_file_id'=>$file->id
            ]);
            $pb->status='sent';
            $pb->save();
        }

        $total_records_C=sprintf("%08s",$paidBills->count() );
        // Batch Control - Record Type Y (80 Character)
        $total_amount_C=str_replace('.','',$total_amount);
        $total_amount_C=sprintf("%014s",$total_amount_C);
        $blank_after_total_amount=str_pad('',56," ",STR_PAD_RIGHT);
        $txt="YC".$total_records_C.$total_amount_C.$blank_after_total_amount."\r";
        fwrite($BatchFile, $txt);
        $total_records_C_2=sprintf("%05s",$paidBills->count() );
        $total_records_D_2=sprintf("%05s",0 );
        $total_records_C=sprintf("%08s",'0' );
        $total_amount_D=sprintf("%014s",'0');
        $blank_after_total_C=str_pad('',41," ",STR_PAD_RIGHT);
        $txt="Z".$total_amount_D.$total_records_D_2.$total_amount_C.$total_records_C_2.$blank_after_total_C."\r";
        fwrite($BatchFile, $txt);



        // start of Writing Report
        $tempData['totalAmount']=$paidBills->sum('amount');
        $tempData['number_of_bills']=$paidBills->count();
        $total_bills=$paidBills->count();
        $reportFile = fopen('eftfiles/c-report.txt', "w") or die("Unable to open file!");
        $txt = "Total Number Of Bills :".$total_bills."\n";

        fwrite($reportFile, $txt);
        $txt = "Total Amount :".$tempData['totalAmount']."$\n";
        fwrite($reportFile, $txt);
        // end of Writing Report
        $files = [
           public_path($full_filename.'.txt'),
            public_path("eftfiles/c-report.txt"),
        ];
        //sasa
        $tempData['files']=$files;
        $tempData['filenumber']=$full_filename;
       Notification::send(User::whereIn('id',app_controllers())->get(),new BillsSentToEft($tempData));

        return redirect(url('bmo/index'))
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Please check you mail system the file to admins.',
                    'type' => 'success',
                ]
            ]);
    }
    public function createDebitEftFile()
    {

//sasas///

        $paidBills=efttransaction::where('status','Pending')->with('user')->get();


        if ($paidBills->count()<=0)
        {
            $tData['type']="EFT-Wallet";
            Notification::send(User::whereIn('id',app_controllers())->get(),new noBillsFoundToProcess($tData));
            return redirect(url('bmo/index'))
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'There is no bill to process.',
                        'type' => 'success',
                    ]
                ]);
        }
        $file_sequence_number='0001';
        if (eft_debit_file::where('id','!=','121e34')->exists())
        {
            $records=eft_debit_file::where('id','!=','121e34')->latest()->first();

            $file_sequence_number=$records->file_sequence;
            $file_sequence_number=intval($file_sequence_number)+1;
        }

        $file_sequence_number=sprintf("%04s", $file_sequence_number);
        $file=eft_debit_file::create([
            'file_sequence'=>$file_sequence_number,
            'type'=>"D",
            'status'=>"Sent"
        ]);


        $total_of_firs_12=0;
        $total_paid_bills=$paidBills->count();


        // start of Writing Batch File
        $fileNumber=$file_sequence_number;
        $filename="D".$file_sequence_number;
        $full_filename='eftfiles/'.$filename;
        $BatchFile = fopen($full_filename.".txt", "w") or die("Unable to open file!");

        $header_date=Carbon::parse(time_now())->addDay();

        $blank_after_data_center=str_pad('',54," ",STR_PAD_RIGHT);
        //File Header - Record Type A (80 Character)

        //Batch Header - Record Type X (80 Character)
        $header_date_year=Carbon::parse($header_date)->format('y');
        $header_date_day=Carbon::parse($header_date)->format('z');

        $txt="AFLUGATORDB".$file_sequence_number.'0'.$header_date_year.$header_date_day.'00120'.$blank_after_data_center."\r";
        fwrite($BatchFile, $txt);
        $shortName="zPAYD";
        $shortName=str_pad($shortName,15," ",STR_PAD_RIGHT);

        $longName="Flugator Inc.";
        $longName=str_pad($longName,30," ",STR_PAD_RIGHT);
        $payable_date=Carbon::parse(time_now())->addDay();
        $payable_date_year=$payable_date->format('y');
        $payable_date_day=$payable_date->format('z');
        $return_account_number="1992680";
        $return_account_number=str_pad($return_account_number,12," ",STR_PAD_RIGHT);
        $txt="XD4300".$payable_date_year.$payable_date_day.$shortName.$longName."000128022".$return_account_number."   \r";
        fwrite($BatchFile, $txt);
        $total_amount=0;
        foreach($paidBills as $index => $pb)
        {


            $payee=$pb->user;
            $amount=$pb->amount;
            $bill_id=sprintf("%04s", $index+1);
            $bill_name=$payee->name;


            $amount=sprintf("%010s",round($pb->amount,2) );

            $amount=str_replace('.','',$amount);
            $amount=sprintf("%010s",$amount);
            $total_amount=$total_amount+$amount;
            $account_number=$longstr = str_pad($pb->account_number, 12);
            $name=str_pad(Str::limit($bill_name,29,''),29);
            $ref=Str::limit(sprintf("%019s",$index ),29,'');
            //Detail Record - 'C' or 'D' (80 Character)

            $txt="D".$amount."0".$pb->institution_number.$pb->transit_number.$account_number.$name.$ref."\r";
            fwrite($BatchFile, $txt);
//            eft_sent_bill::create([
//                'record_id'=>"Bill-".$bill_id,
//                'paid_bill_id'=>$pb->id,
//                'source'=>'bill',
//                'eft_file_id'=>$file->id
//            ]);
            $pb->record_id=$ref;
            $pb->eft_file_id=$file->id;
            $pb->status='sent';
            $pb->save();
        }

        $total_records_D=sprintf("%08s",$paidBills->count() );
        // Batch Control - Record Type Y (80 Character)
        $total_amount_D=str_replace('.','',$total_amount);
        $total_amount_D=sprintf("%014s",$total_amount_D);
        $blank_after_total_amount=str_pad('',56," ",STR_PAD_RIGHT);
        $txt="YD".$total_records_D.$total_amount_D.$blank_after_total_amount."\r";
        fwrite($BatchFile, $txt);
        $total_records_D_2=sprintf("%05s",$paidBills->count() );
        $total_records_C_2=sprintf("%05s",0 );
        $total_amount_C=sprintf("%014s",'0');
        $blank_after_total_C=str_pad('',41," ",STR_PAD_RIGHT);
        $txt="Z".$total_amount_D.$total_records_D_2.$total_amount_C.$total_records_C_2.$blank_after_total_C."\r";
        fwrite($BatchFile, $txt);



        // start of Writing Report
        $tempData['totalAmount']=$paidBills->sum('amount');
        $tempData['number_of_bills']=$paidBills->count();
        $total_bills=$paidBills->count();
        $reportFile = fopen('eftfiles/d-report.txt', "w") or die("Unable to open file!");
        $txt = "Total Number Of Bills :".$total_bills."\n";

        fwrite($reportFile, $txt);
        $txt = "Total Amount :".$tempData['totalAmount']."$\n";
        fwrite($reportFile, $txt);
        // end of Writing Report
        $files = [
            public_path($full_filename.'.txt'),
            public_path("eftfiles/d-report.txt"),
        ];
        //sasa
        $tempData['files']=$files;
        $tempData['filenumber']=$full_filename;

       Notification::send(User::whereIn('id',app_controllers())->get(),new WalletTransactionsSentToEft($tempData));
//
        return redirect(url('bmo/index'))
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Please check you mail system the file to admins.',
                    'type' => 'success',
                ]
            ]);
    }
    public function createtelpayFile()
    {
        //sddas

        $paidBills=PaidBill::where('status','amount-received')->where('is_sent_to_pay','Pending')->where('type','service')->with('payee')->take(9999)->get();

        if ($paidBills->count()<=0)
        {
            $tData['type']="Telpay";
           Notification::send(User::whereIn('id',app_controllers())->get(),new noBillsFoundToProcess($tData));
            return redirect(url('telpay/index'))
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'There is no bill to process.',
                        'type' => 'success',
                    ]
                ]);
        }
        $file_sequence_number='001';
        if (telpay_file::where('id','!=','121e34')->exists())
        {
            $records=telpay_file::where('id','!=','121e34')->latest()->first();

            $file_sequence_number=$records->file_sequence;
            $file_sequence_number=intval($file_sequence_number)+1;
        }

        $file_sequence_number=sprintf("%03s", $file_sequence_number);
        $file=telpay_file::create([
            'file_sequence'=>$file_sequence_number,
            'status'=>"Sent"
        ]);


        $total_of_firs_12=0;
        $total_paid_bills=$paidBills->count();
//        PaidBill::where('status','Pending')->update([
//            'status'=>'Canceled'
//        ]);

        // start of Writing Batch File
        $fileNumber=$file_sequence_number;
        $filename="OI005350";
        //dadasdasd
        $full_filename='telpayfiles/'.$filename.'.'.$fileNumber;
        $BatchFile = fopen($full_filename,'wb') or die("Unable to open file!");
        $header_date=Carbon::parse(time_now())->format('Ymdhi');
//        $txt = "PC00000005350  2801884F001 B430       ".$header_date." asas                                                                         \n";

//        $header_date=str_pad($header_date,59," ",STR_PAD_RIGHT);
        $txt="PC00000005350  2801884F".$file_sequence_number." B430        ".$header_date."                                                                                                                                                     "."\r\n";
        fwrite($BatchFile, $txt);
//        $txt = "\n";asdadasd
//        fwrite($BatchFile, $txt);
        $tota_Amount=0;

        foreach($paidBills as $index => $pb)
        {

            $amount=sprintf("%010s",number_format($pb->amount,2) );
            $amount=str_replace('.','',$amount);
            $bill_id=sprintf("%04s", $index+1);
            $system_bill_id=sprintf("%010s", $pb->id);
            $d=substr($pb->payee->account_number,0,30);
            echo "Acount :".$pb->payee->account_number."<br>";
            echo "Modified Account: ".self::convertAccountNumberToDigitsOnly($pb->payee->account_number)."<br>";
            $total_of_firs_12=$total_of_firs_12+self::convertAccountNumberToDigitsOnly($pb->payee->account_number);
            $pb->payee->account_number= str_pad($pb->payee->account_number,30," ",STR_PAD_RIGHT);
            $pb->user->name= str_pad($pb->user->name,30," ",STR_PAD_RIGHT);
//           dd($pb->payee->account_number);
            $pb->payee->account_number=strtoupper($pb->payee->account_number);
            $spaces_after_name="";
            $tota_Amount=$tota_Amount+$amount;

            $spaces_after_name=str_pad($spaces_after_name,99," ",STR_PAD_RIGHT);
            $nText="PC3".$bill_id."         ".$pb->payee->code." ".$amount." 00000ACT".$pb->payee->account_number.$pb->user->name.$spaces_after_name."\r\n";
//            $nText = "PC00000005350    2801884f001    B430    ".$header_date."\n";
            fwrite($BatchFile, $nText);

//          if (!telpaysentBill::where(['record_id'=>"PC3".$bill_id,'paid_bill_id'=>$pb->id])->exists())
//          {
            telpaysentBill::create([
                'record_id'=>"PC3".$bill_id,
                'paid_bill_id'=>$pb->id,
                'telpay_file_id'=>$file->id
            ]);
            $pb->is_sent_to_pay='sent';
            $pb->save();
            //sadasdas
//          }
        }
//            dd($total_of_firs_12);
        echo "Sum of first 12 Digits of paid Bills :".$total_of_firs_12."<br>";
        $total_paid_bills_Count_withHeader=sprintf("%05s",$total_paid_bills+2);
        $total_paid_bills=sprintf("%04s",$total_paid_bills );
        $tota_Amount=sprintf("%013s",$tota_Amount);
        // adding total of first 12 digit of all bills to total amount
        $total_of_firs_12=$total_of_firs_12+intval(substr($tota_Amount,1,13));
        $total_of_firs_12=sprintf("%012s",$total_of_firs_12);

        $tl=strlen($total_of_firs_12);
        $tl_2=$tl-12;

        $trailer_hash=substr($total_of_firs_12,$tl_2,$tl);

        $spaces_after_amount=str_pad('',93,"0",STR_PAD_RIGHT);
//        $trailer_hash=str_pad('',12,"#",STR_PAD_RIGHT);
        $blank_after_trailer=str_pad('',25," ",STR_PAD_RIGHT);

        echo "total_paid_bills_Count_withHeader : ".$total_paid_bills_Count_withHeader."<br>";
        echo "total_paid_bills : ".$total_paid_bills."<br>";
        echo "Total Amount: ".$tota_Amount."<br>";
        echo "Hash : ".$trailer_hash."<br>";

//        echo "Total Number Of Bills :".$trailer_hash."<br>"  asas;
        $txt="PCZZZZZ".$total_paid_bills_Count_withHeader.$total_paid_bills.$tota_Amount.$spaces_after_amount.$trailer_hash.$blank_after_trailer."Y\r\n";
        fwrite($BatchFile, $txt);
        // end of Writing Batch File


        // start of Writing Report
        $tempData['totalAmount']=$paidBills->sum('amount');
        $tempData['number_of_bills']=$paidBills->count();
        $total_bills=$paidBills->count();
        $reportFile = fopen('telpayfiles/report.txt', "w") or die("Unable to open file!");
        $txt = "Total Number Of Bills :".$total_bills."\n";

        fwrite($reportFile, $txt);
        $txt = "Total Amount :".$tempData['totalAmount']."$\n";
        fwrite($reportFile, $txt);
        // end of Writing Report


        $files = [
            public_path($full_filename),
            public_path($full_filename),
            public_path("telpayfiles/report.txt"),
        ];
        //sasa
        $tempData['files']=$files;

        $tempData['filenumber']=$full_filename;

       Notification::send(User::whereIn('id',app_controllers())->get(),new BillsSentToTelpay($tempData));

        return redirect(url('telpay/index'))
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Please check you mail system the file to admins.',
                    'type' => 'success',
                ]
            ]);
    }
    public static function convertAccountNumberToDigitsOnly($str)
    {
        $newStr='';
        $str=strtoupper($str);
        $array=str_split($str);
        foreach ($array as $item)
        {

            if (intval($item)==0)
            {

                $newStr=$newStr.self::alphaToNumber($item);

            }else
            {
                $newStr=$newStr.$item;
            }

        }

        if (strlen($newStr)<12)
        {
            $length=12-strlen($newStr);
        }

        $newStr= str_pad($newStr,12,"0",STR_PAD_RIGHT);
        $newStr=substr($newStr, 0, 12);

        return $newStr;
    }
    public static function alphaToNumber($alpha)
    {
        if ($alpha=="A" || $alpha=="K" || $alpha=="U")
        {
            return 0;
        }
       else if ($alpha=="B" || $alpha=="L" || $alpha=="V")
        {
            return 1;
        }
        else if ($alpha=="C" || $alpha=="M" || $alpha=="W")
        {
            return 2;
        }
        else if ($alpha=="D" || $alpha=="N" || $alpha=="X")
        {
            return 3;
        }
        else if ($alpha=="E" || $alpha=="O" || $alpha=="Y")
        {
            return 4;
        }
        else if ($alpha=="F" || $alpha=="P" || $alpha=="Z")
        {
            return 5;
        }
        else if ($alpha=="G" || $alpha=="Q")
        {
            return 6;
        }
        else if ($alpha=="H" || $alpha=="R")
        {
            return 7;
        }
        else if ($alpha=="I" || $alpha=="S" )
        {
            return 8;
        }
        else if ($alpha=="J" || $alpha=="T")
        {
            return 9;
        }
        else
        {
            return 0;
        }

    }
}
