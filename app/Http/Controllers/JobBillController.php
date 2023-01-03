<?php

namespace App\Http\Controllers;

use App\Jobs\BillFailMailJob;
use App\Models\Bill;
use App\Models\PaidBill;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class JobBillController extends Controller
{
    public $user = '';
    public $bill = '';
    public $today_date = '';

    public function __construct()
    {
        $this->today_date = Carbon::now()->addDays(2)->format('Y-m-d');
    }

    public  function index(Request $request)
    {
        ini_set('max_execution_time', 0);

        $today_date = $this->today_date;
        $users = $users = User::role('user')
            ->with(['bills' => function ($q) use ($today_date) {
                $q->where('effective_from', '<=', $today_date)
                    ->where('status', 'active');
            }])
            ->get();
        foreach ($users as $user) {
            $this->user = $user;
            foreach ($user->bills as $bill) {
                $this->bill = $bill;
                $user_balance = $user->balance + $user->my_lender_balance;
//                if ($user_balance >= $bill->amount) {
                switch ($bill->frequency) {
                    case 'once':
                        if ($bill->due_date == $today_date && !$bill->number_of_paids) {
                            $this->payBill();
                        }
                        break;
                    case 'weekly':
                        if ($bill->due_date <= $today_date) {
                            if ($bill->last_paid_date) {
                                $last_paid_date_carbon = Carbon::make($bill->last_paid_date);
                                switch ($bill->bill_duration) {
                                    case 'ongoing':
                                        if ($last_paid_date_carbon->addWeek(1)->format('Y-m-d') == $today_date) {
                                            $this->payBill();
                                        }
                                        break;
                                    case 'number_bills':
//                                            if number of bills are less then paid bills
                                        if ($bill->number_of_bills > $bill->number_of_paids) {
                                            if ($last_paid_date_carbon->addWeek(1)->format('Y-m-d') == $today_date) {
                                                $this->payBill();
                                            }
                                        }
                                        break;
                                }
                            } else {
                                if ($bill->due_date == $today_date) {
                                    $this->payBill();
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
                                            $this->payBill();
                                        }
                                        break;
                                    case 'number_bills':
//                                            if number of bills are less then paid bills
                                        if ($bill->number_of_bills > $bill->number_of_paids) {
                                            if ($last_paid_date_carbon->addWeeks(2)->format('Y-m-d') == $today_date) {
                                                $this->payBill();
                                            }
                                        }
                                        break;
                                }
                            } else {
                                if ($bill->due_date == $today_date) {
                                    $this->payBill();
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
                                                $this->payBill();
                                            }
                                            break;
                                        case 'number_bills':
//                                            if number of bills are less then paid bills
                                            if ($bill->number_of_bills > $bill->number_of_paids) {
                                                if ($last_paid_date_carbon->addMonth(1)->format('d') == date('d')) {
                                                    $this->payBill();
                                                }
                                            }
                                            break;
                                    }
                                }
                            } else {
                                if (date('m', strtotime($bill->last_paid_date)) != date('m', strtotime($today_date))) {
                                    $this->payBill();
                                }
                            }
                        }
                        break;
                }
//                }
            }
        }
        echo 'success';
    }

    public function payBill()
    {
        $today_date = $this->today_date;
        $bill = $this->bill;

        $user = $this->user;
        $user_balance = $user->balance + $user->my_lender_balance;
        if ($user_balance >= $bill->amount) {
            $bill_amount = $bill->amount;
            $bill_interest_amount = $this->calculateInterest($bill_amount, $user->interest_rate);
            if ($user->balance > 0) {
                if ($user->balance >= $bill_amount) {
                    $user->balance -= $bill_amount;
                    $user->bill_paid_balance += $bill_amount;
                    PaidBill::create([
                        'bill_id' => $bill->id,
                        'user_id' => $user->id,
                        'amount' => $bill->amount,
                        'date' => $this->today_date,
                        'vendor_bill_category_id' => $bill->vendor_bill_category_id,
                        'month' => date('m', strtotime($today_date)),
                        'year' => date('Y', strtotime($today_date)),
                        'interest_amount' => 0,
                        'from_account' => 'personal',
                    ]);
                    $this->updateLastBillDetails();
                } elseif ($user->balance < $bill_amount) {
                    $remaining_balance = $bill_amount - $user->balance;
                    $remaining_bill_interest = $this->calculateInterest($remaining_balance, $user->interest_rate);
                    if ($user->my_lender_balance >= ($remaining_balance + $remaining_bill_interest)) {
                        $user->balance = 0;
                        $user->my_lender_balance -= ($remaining_balance + $remaining_bill_interest);
                        $user->bill_paid_balance += $bill_amount + $remaining_bill_interest;
                        $user->interest_balance += $remaining_bill_interest;
                        PaidBill::create([
                            'bill_id' => $bill->id,
                            'user_id' => $user->id,
                            'amount' => $bill->amount,
                            'date' => $this->today_date,
                            'vendor_bill_category_id' => $bill->vendor_bill_category_id,
                            'month' => date('m', strtotime($today_date)),
                            'year' => date('Y', strtotime($today_date)),
                            'interest_amount' => $remaining_bill_interest,
                            'from_account' => 'personal',
                        ]);
                        if ($user->lender) {
                            $user->lender->interest_balance += $remaining_bill_interest;
                            $user->lender->save();
                        }
                        $this->updateLastBillDetails();
                    }
                }
            } elseif ($user->my_lender_balance && $user->my_lender_balance >= ($bill_amount + $bill_interest_amount)) {
                $user->my_lender_balance -= ($bill_amount + $bill_interest_amount);
                PaidBill::create([
                    'bill_id' => $bill->id,
                    'user_id' => $user->id,
                    'amount' => $bill->amount,
                    'date' => $this->today_date,
                    'vendor_bill_category_id' => $bill->vendor_bill_category_id,
                    'month' => date('m', strtotime($today_date)),
                    'year' => date('Y', strtotime($today_date)),
                    'interest_amount' => $bill_interest_amount,
                    'from_account' => 'lender',
                ]);
                if ($user->lender) {
                    $user->lender->interest_balance += $bill_interest_amount;
                    $user->lender->save();
                }
                $this->updateLastBillDetails();
            }
            $user->save();
        } else {
            dispatch(new BillFailMailJob($user->email));
        }
    }

    public function calculateInterest($amount, $interest_rate)
    {
        return ($amount * $interest_rate) ? round(($amount * $interest_rate) / 100, 2) : 0;
    }

    public function updateLastBillDetails()
    {
        $bill = $this->bill;
        $bill->last_paid_date = $this->today_date;
        $bill->number_of_paids += 1;
        $bill->save();
    }

    public function indexOld(Request $request)
    {
        $today_date = $this->today_date;
        $today_carbon = Carbon::make($today_date);
//dd($today_carbon->addWeek(1)->format('Y-m-d'));
//        dd($today_carbon);
        $users = $users = User::role('user')
            ->where('balance', '>', 0)
            ->orWhere('my_lender_balance', '>', 0)
//            ->whereDate('subscription_end', '>=', date('Y-m-d'))
            ->with(['bills' => function ($q) use ($today_date) {
                $q->where('effective_from', '<=', $today_date)
                    ->where('status', 'active');
            }])
            ->get();
        foreach ($users as $user) {
            $this->user = $user;
            foreach ($user->bills as $bill) {
                if (($user->balance && $user->balance >= $bill->amount && $bill->amount) || ($user->my_lender_balance && $user->my_lender_balance >= $bill->amount && $bill->amount)) {
                    $this->bill = $bill;
                    switch ($bill->frequency) {
                        case 'once':
                            if ($bill->due_date == $today_date && !$bill->number_of_paids) {
                                $this->payBill();
                            }
                            break;
                        case 'weekly':
                            if ($bill->due_date <= $today_date) {
                                if ($bill->last_paid_date) {
                                    $last_paid_date_carbon = Carbon::make($bill->last_paid_date);
                                    switch ($bill->bill_duration) {
                                        case 'ongoing':
                                            if ($last_paid_date_carbon->addWeek(1)->format('Y-m-d') == $today_date) {
                                                $this->payBill();
                                            }
                                            break;
                                        case 'number_bills':
//                                            if number of bills are less then paid bills
                                            if ($bill->number_of_bills > $bill->number_of_paids) {
                                                if ($last_paid_date_carbon->addWeek(1)->format('Y-m-d') == $today_date) {
                                                    $this->payBill();
                                                }
                                            }
                                            break;
                                    }
                                } else {
                                    if ($bill->due_date == $today_date) {
                                        $this->payBill();
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
                                                $this->payBill();
                                            }
                                            break;
                                        case 'number_bills':
//                                            if number of bills are less then paid bills
                                            if ($bill->number_of_bills > $bill->number_of_paids) {
                                                if ($last_paid_date_carbon->addWeeks(2)->format('Y-m-d') == $today_date) {
                                                    $this->payBill();
                                                }
                                            }
                                            break;
                                    }
                                } else {
                                    if ($bill->due_date == $today_date) {
                                        $this->payBill();
                                    }
                                }
                            }
                            break;
                        case 'monthly':
                            if ($bill->due_day == date('d')) {
                                if ($bill->last_paid_date) {
                                    if (date('m', strtotime($bill->last_paid_date)) != date('m')) {
                                        $last_paid_date_carbon = Carbon::make($bill->last_paid_date);
                                        switch ($bill->bill_duration) {
                                            case 'ongoing':
                                                if ($last_paid_date_carbon->addMonth(1)->format('d') == date('d')) {
                                                    $this->payBill();
                                                }
                                                break;
                                            case 'number_bills':
//                                            if number of bills are less then paid bills
                                                if ($bill->number_of_bills > $bill->number_of_paids) {
                                                    if ($last_paid_date_carbon->addMonth(1)->format('d') == date('d')) {
                                                        $this->payBill();
                                                    }
                                                }
                                                break;
                                        }
                                    }
                                } else {
                                    if (date('m', strtotime($bill->last_paid_date)) != date('m')) {
                                        $this->payBill();
                                    }
                                }
                            }
                            break;
                    }
                }
            }
        }
//        return $users;
    }

    public function payBillOld()
    {
        $bill = $this->bill;
        $user = $this->user;
        $bill_interest_amount = ($bill->amount * $user->interest_rate) ? (($bill->amount * $user->interest_rate) / 100) : 0;

        $paid_bill = PaidBill::create([
            'bill_id' => $bill->id,
            'user_id' => $user->id,
            'amount' => $bill->amount,
            'vendor_bill_category_id' => $bill->vendor_bill_category_id,
            'month' => date('m'),
            'year' => date('Y'),
            'interest_amount' => $bill_interest_amount,
        ]);

        $bill->last_paid_date = $this->today_date;
        $bill->number_of_paids += 1;
        $bill->save();

        if ($user->balance >= $bill->amount) {
            $user->balance -= $bill->amount;
            $paid_bill->from_account = 'personal';
        } elseif ($user->my_lender_balance && $user->my_lender_balance) {
            $user->my_lender_balance -= $bill->amount;
            $paid_bill->from_account = 'lender';
        }

        $user->bill_paid_balance += $bill->amount;
        $user->interest_balance += $bill_interest_amount;
        $paid_bill->save();
        $user->save();
    }

}
