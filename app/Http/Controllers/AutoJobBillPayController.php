<?php

namespace App\Http\Controllers;

use App\Jobs\BillFailMailJob;
use App\Jobs\createBillPaymentOrderJob;
use App\Jobs\sendDataToAccountingServices;
use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\LocBankAccount;
use App\Models\packageTransaction;
use App\Models\PaidBill;
use App\Models\User;
use App\Notifications\billChargedFromCardInsteadWallet;
use App\Notifications\billPaymentStartedUsingWallet;
use App\Notifications\noBillPaymentMethodFound;
use App\Notifications\oneday_before_charging_card_bill_payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;


class AutoJobBillPayController extends Controller
{
    public static $user = '';
    public static $bill = '';
    public static $today_date = '';

    public  function __construct()
    {
//        $this->today_date = Carbon::now()->addDays(2)->format('Y-m-d');
//        self::$today_date = Carbon::now()->format('Y-m-d');
    }
 public static function setTodayDate()
 {
    self::$today_date = Carbon::now()->subDays(1)->format('Y-m-d');

 }
    public static  function index()
    {

        ini_set('max_execution_time', 0);
        self::setTodayDate();
        $today_date = today_date();

        $today_date=Carbon::parse($today_date)->addDay()->toDateString();

        $users  = User::role('user')
            ->with(['bills' => function ($q) use ($today_date) {
                $q->where('effective_from', '<=', $today_date)
                    ->where('status', 'active');
            }])
            ->get();

        foreach ($users as $user) {

//asasasas
            self::payUserbills($user,$is_paynow=false);

        }
        echo 'Auto Bill checker Successfully Completed';
    }

    public static function payBill($bill,$user,$today_date)
    {


            try {

                   createBillPaymentOrderJob::dispatch($bill,$user,$today_date);

                   self::updateLastBillDetails($bill,$today_date);


            } catch(\Exception $e) {
//                dd($e);
//                dispatch(new BillFailMailJob($user->email));
            }



    }

    public static function  calculateInterest($amount, $interest_rate)
    {
        return ($amount * $interest_rate) ? round(($amount * $interest_rate) / 100, 2) : 0;
    }
    public static function updateLastBillDetails($bill,$today_date)
    {

        $bill->last_paid_date =$today_date;
        $bill->number_of_paids += 1;
        $bill->oneday_before_card_payment_email="false";
        $bill->save();
    }

  public static function payUserBills($user)
  {


      self::setTodayDate();
      $today_date =today_date();

      foreach ($user->bills as $bill) {



          if ($bill->deleted_at==null) {

              $userBalance=my_wallet_balance($user->id);
              $charged_amount=$bill->amount;
              if ($bill->payee->payee_id!=null)
              {
                  $charged_amount=$charged_amount+eft_commission();
              }


              if (self::get_next_paying_date_of_bill($bill,$today_date)  && $bill->oneday_before_card_payment_email!="true" && $userBalance < $charged_amount)
              {

                  echo $bill->id;
                  $tempData['user']=$user;
                  Notification::route('mail', $user->email)->notify(new oneday_before_charging_card_bill_payment($tempData));
                  $nbill=Bill::find($bill->id);
                  $nbill->oneday_before_card_payment_email="true";
                  $nbill->save();

              }

              switch ($bill->frequency) {
                  case 'once':

                      if ( $bill->due_date == $today_date && !$bill->number_of_paids) {

                          self::payBill($bill,$user,$today_date);
                      }
                      break;
                  case 'weekly':
                      if ($bill->due_date <= $today_date) {
                          if ($bill->last_paid_date) {
                              $last_paid_date_carbon = Carbon::make($bill->last_paid_date);
                              switch ($bill->bill_duration) {
                                  case 'ongoing':
                                      if ($last_paid_date_carbon->addWeek(1)->format('Y-m-d') == $today_date) {
                                          self::payBill($bill,$user,$today_date);
                                      }
                                      break;
                                  case 'number_bills':
//                                            if number of bills are less then paid bills
                                      if ($bill->number_of_bills > $bill->number_of_paids) {
                                          if ($last_paid_date_carbon->addWeek(1)->format('Y-m-d') == $today_date) {
                                              self::payBill($bill,$user,$today_date);
                                          }
                                      }
                                      break;
                              }
                          } else {
                              if ($bill->due_date == $today_date) {
                                  self::payBill($bill,$user,$today_date);
                              }
                          }
                      }
                      break;
                  case 'biweekly':
                      if ($bill->due_date <= $today_date) {
                          if ($bill->last_paid_date) {
                              $last_paid_date_carbon = Carbon::make($bill->last_paid_date);
                              switch ($bill->bill_duration) {
                                  case 'ongoing':
                                      if ($last_paid_date_carbon->addWeeks(2)->format('Y-m-d') == $today_date) {
                                          self::payBill($bill,$user,$today_date);
                                      }
                                      break;
                                  case 'number_bills':
//                                            if number of bills are less then paid bills
                                      if ($bill->number_of_bills > $bill->number_of_paids) {
                                          if ($last_paid_date_carbon->addWeeks(2)->format('Y-m-d') == $today_date) {
                                              self::payBill($bill,$user,$today_date);
                                          }
                                      }
                                      break;
                              }
                          } else {
                              if ($bill->due_date == $today_date) {
                                  self::payBill($bill,$user,$today_date);
                              }
                          }
                      }
                      break;
                  case 'monthly':
                      if ($bill->due_day == date('d', strtotime($today_date))) {
                          if ($bill->last_paid_date) {
                              if (date('m', strtotime($bill->last_paid_date)) != date('d', strtotime($today_date))) {
                                  $last_paid_date_carbon = Carbon::make($bill->last_paid_date);
                                  switch ($bill->bill_duration) {
                                      case 'ongoing':
                                          if ($last_paid_date_carbon->addMonth(1)->format('d') == date('d')) {
                                              self::payBill($bill,$user,$today_date);
                                          }
                                          break;
                                      case 'number_bills':
//                                            if number of bills are less then paid bills
                                          if ($bill->number_of_bills > $bill->number_of_paids) {
                                              if ($last_paid_date_carbon->addMonth(1)->format('d') == date('d')) {
                                                  self::payBill($bill,$user,$today_date);
                                              }
                                          }
                                          break;
                                  }
                              }
                          } else {
                              if (date('m', strtotime($bill->last_paid_date)) != date('m', strtotime($today_date))) {
                                  self::payBill($bill,$user,$today_date);
                              }
                          }
                      }
                      break;
              }
          }
      }
  }
  public static function get_next_paying_date_of_bill($bill,$today_date)
  {

      $today_date=Carbon::parse($today_date)->addDay()->timezone(Config::get('app.timezone'))->format('Y-m-d');;


//sdfsdfsdasdasd
      $nextDate=false;

      switch ($bill->frequency) {
          case 'once':
              if ($bill->due_date == $today_date && !$bill->number_of_paids) {

                  $nextDate=true;

              }
              break;
          case 'weekly':
              if ($bill->due_date <= $today_date) {
                  if ($bill->last_paid_date) {
                      $last_paid_date_carbon = Carbon::make($bill->last_paid_date);
                      switch ($bill->bill_duration) {
                          case 'ongoing':
                              if ($last_paid_date_carbon->addWeek(1)->format('Y-m-d') == $today_date) {
                                  $nextDate=true;
                              }
                              break;
                          case 'number_bills':
//                                            if number of bills are less then paid bills
                              if ($bill->number_of_bills > $bill->number_of_paids) {
                                  if ($last_paid_date_carbon->addWeek(1)->format('Y-m-d') == $today_date) {
                                      $nextDate=true;
                                  }
                              }
                              break;
                      }
                  } else {
                      if ($bill->due_date == $today_date) {
                          $nextDate=true;
                      }
                  }
              }
              break;
          case 'biweekly':
              if ($bill->due_date <= $today_date) {
                  if ($bill->last_paid_date) {
                      $last_paid_date_carbon = Carbon::make($bill->last_paid_date);
                      switch ($bill->bill_duration) {
                          case 'ongoing':
                              if ($last_paid_date_carbon->addWeeks(2)->format('Y-m-d') == $today_date) {
                                  $nextDate=true;
                              }
                              break;
                          case 'number_bills':
//                                            if number of bills are less then paid bills
                              if ($bill->number_of_bills > $bill->number_of_paids) {
                                  if ($last_paid_date_carbon->addWeeks(2)->format('Y-m-d') == $today_date) {
                                      $nextDate=true;
                                  }
                              }
                              break;
                      }
                  } else {
                      if ($bill->due_date == $today_date) {
                          $nextDate=true;
                      }
                  }
              }
              break;
          case 'monthly':
              if ($bill->due_day == date('d', strtotime($today_date))) {
                  if ($bill->last_paid_date) {
                      if (date('m', strtotime($bill->last_paid_date)) != date('d', strtotime($today_date))) {
                          $last_paid_date_carbon = Carbon::make($bill->last_paid_date);
                          switch ($bill->bill_duration) {
                              case 'ongoing':
                                  if ($last_paid_date_carbon->addMonth(1)->format('d') == date('d')) {
                                      $nextDate=true;
                                  }
                                  break;
                              case 'number_bills':
//                                            if number of bills are less then paid bills
                                  if ($bill->number_of_bills > $bill->number_of_paids) {
                                      if ($last_paid_date_carbon->addMonth(1)->format('d') == date('d')) {
                                          $nextDate=true;
                                      }
                                  }
                                  break;
                          }
                      }
                  } else {
                      if (date('m', strtotime($bill->last_paid_date)) != date('m', strtotime($today_date))) {
                          $nextDate=true;
                      }
                  }
              }
              break;
      }

      return $nextDate;
  }
    public static function payBillNow($bill,$user,$today)
    {

        $payment_method='wallet';
        $status="Pending";
        $p_s_m_c_amount=0;
        $p_c_m_c_amount=0;
        $userBalance=my_wallet_balance($user->id);
        $type='service';
        $charged_amount=$bill->amount;
        if ($bill->payee->payee_id!=null)
        {
            $type='self-added';
            $charged_amount=$charged_amount+eft_commission();
        }
        if ($userBalance<$charged_amount)
        {
            $payment_method='card';
            $p_c_m_c_amount=($bill->amount/100)*square_commission();
            $status="Pending";
        }else
        {
            $user->wallet_balance=$user->wallet_balance-$charged_amount;
            $user->save();
            $status='amount-received';
            $tempData['date']=$today;
            Notification::send(User::where('id',$user->id)->get(),new billPaymentStartedUsingWallet($tempData));

        }

//        sendDataToAccountingServices::dispatch($user,$bill);
    //    AccountingController::sendDataToServices($user,$bill->payee->nickname,$bill->due_date,$bill->amount);
          $amount=$bill->amount;
        if ($type=='self-added')
        {
            //sdasdas
            $p_s_m_c_amount=eft_commission();
//            $amount=$amount+eft_commission();
//            $amount=round($amount,2);
        }
        $pBill= PaidBill::create([
            'bill_id' => $bill->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'date' => $today,
            'actual_amount'=>$bill->amount,
            'type'=>$type,
            'payment_method'=>$payment_method,
            'vendor_bill_category_id' => 12,
            'month' => date('m', strtotime($today)),
            'year' => date('Y', strtotime($today)),
            'interest_amount' => 0,
            'from_account' => 'personal',
            'status'=>$status,
            'p_c_m_c_amount'=>$p_c_m_c_amount,
            'p_s_m_c_amount'=>$p_s_m_c_amount,
            'payee_id'=>$bill->payee_id
        ]);

//        self::updateLastBillDetails();
        if ($userBalance<$bill->amount) {

            if (LocBankAccount::where(['user_id' => $user->id, 'is_primary' => true])->exists()) {

                $credit_card = LocBankAccount::where(['user_id' => $user->id, 'is_primary' => true])->first();
                $actualAmount=$bill->amount;
                $oamount=round($bill->amount);
                $amount=$charged_amount+($charged_amount/100)*square_commission();


                $amount=round($amount,2);
                if (!is_float($amount)) {
                    $amount = floatval($amount);
                }

                $amount = $amount * 100;


                if ($credit_card) {
                    //sdasd

                    $req = Http::withToken(ENVController::$access_token)->post(ENVController::$sqaure_url . '/v2/payments', [
                        'idempotency_key' => str_random(20),
                        'autocomplete' => true,
                        'amount_money' => [
                            'amount' => $amount,
                            'currency' => "CAD"
                        ],
                        'source_id' => $credit_card->bank_id,
                        'customer_id' => $user->sqaure_customer_id
                    ]);
//                    echo "ok".$req->status();
//asassasa

//                    dd($amount);
                    if ($req->status() == 200) {


//                        echo "Card Payment Done";
                        $re4= packageTransaction::create([
                            'user_id' => $user->id,
                            'package_id' => 0,
                            'amount' => $amount,
                            'bill_id'=>$pBill->id,
                            'type' => 'bill_charge',
                            'transaction_id' => $req['payment']['id'],
                            'duration' => ''
                        ]);

                        $ndata['user']=$user;
                        $ndata['amount']=$actualAmount;
//                        echo "Email Seding...";
                        Notification::send(User::where('id',$user->id)->get(),new billChargedFromCardInsteadWallet($ndata));

//                        echo "Email Sent";
                    }
//               dd($req->json());ss
                }
            }
            else
            {

                $tempData['user']='';
                Notification::send(User::where('id',$user->id)->get(),new noBillPaymentMethodFound($tempData));


            }
        }


    }
}
