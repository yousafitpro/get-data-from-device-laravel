<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\PaidBill;
use App\Models\User;
use App\Models\UserApplication;
use App\Models\VendorBill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DailyPayoutController extends Controller
{
    public $user = '';
    public $bill = '';
    public $today_date = '';
    public $bills = [];

    public $VIEW = 'admin.daily-payouts';
    public $TITLE = 'Daily Payouts';
    public $URL = 'admin/daily-payouts';

    public function __construct()
    {
        view()->share([
            'title' => $this->TITLE,
            'url' => url($this->URL),
        ]);
    }

    public function index(Request $request)
    {
        return view($this->VIEW . '.index');
    }

    public function store(Request $request)
    {
        $date = $request->date;
        $date = Carbon::make($date)->addDays(2)->format('Y-m-d');
        $paid_bills = PaidBill::where('date', $date)->with('bill')->get();
        $bills = [];
        foreach ($paid_bills as $bill) {
            $bills [] = [
                'Bill Transaction Number' => $bill->bill->bmo_transaction_id,
                'Biller Account Number' => $bill->bill->bmo_title,
                'Date' => date('Y/m/d', strtotime($date)),
                'Amount' => $bill->amount,
                'Transit' => '28022',
                'Account Number' => '1992680',
            ];
        }
        if (count($bills)) {
            return exportCsv($bills, 'daily payouts');
        }
        return back()
            ->withInput()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => $this->TITLE . ' not found any record',
                    'type' => 'success',
                ]
            ]);
    }

    public function payBill()
    {
        $bill = $this->bill;
        $this->bills[] = [
            'Bill Transaction Number' => $bill->bmo_transaction_id,
            'Biller Account Number' => $bill->bmo_title,
            'Date' => date('Y/m/d', strtotime($this->today_date)),
            'Amount' => $bill->amount,
            'Transit' => '',
            'Account Number' => '',
        ];
    }

}
