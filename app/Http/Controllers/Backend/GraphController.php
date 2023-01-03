<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\ServiceSuggestionMail;
use App\Models\Business;
use App\Models\Lead;
use App\Models\PaidBill;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserApplication;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Mail;

class GraphController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->role('user')) {
            $deposits = Transaction::where('receiver_id', $user->id)->get();
            $personal_deposits = numberFormat($deposits->where('remarks', 'Personal Fund')->sum('amount'));
            $lender_deposits = numberFormat(UserApplication::where('user_id', $user->id)->where('status', 'Approved')->sum('lender_loan_amount'));
            $lender_payments = numberFormat($deposits->where('remarks', 'Pay Lender')->sum('amount'));
            $accrued_interest = numberFormat(PaidBill::where('user_id', $user->id)->sum('interest_amount'));
            $bill_payments = numberFormat(PaidBill::where('user_id', $user->id)->sum('amount'));
            $total_deposits = numberFormat($lender_deposits + $personal_deposits + $lender_payments);
         $interest=(auth()->user()->my_lender_balance/100)*auth()->user()->interest_rate;
          $total=auth()->user()->my_lender_balance+$interest;

            $amount_deposits = [
               round((auth()->user()->my_lender_balance/$total)*10,2),
                round(($interest/$total)*10,2),
                0,
//                $lender_payments,
//                $accrued_interest,
//                $bill_payments
            ];
            $percent_values = [];
            $max_amount = max($amount_deposits);
            foreach ($amount_deposits as $amount_deposit) {
                if ($max_amount) {
                    array_push($percent_values, numberFormat(($amount_deposit * 100) / $max_amount));
                } else {
                    array_push($percent_values, $amount_deposit);
                }
            }

            return [
                'deposits' => $amount_deposits,
                'percent_deposits' => $percent_values,
            ];

        }
    }

    public function getTotal(Request $request)
    {
        $from_date = $request->from_date ?: date('Y-m-d', strtotime('-1 month'));
        $to_date = $request->to_date ?: date('Y-m-d');
        $stock_graph_labels = $this->generateDateRange($from_date, $to_date);
        $stock_graph_purchase = [];
        $stock_graph_production = [];

        foreach ($stock_graph_labels as $stock_graph_label) {
            $stock_graph_purchase[$stock_graph_label] = 0;
            $stock_graph_production[$stock_graph_label] = 0;
        }

        $total_purchase = 0;
        $purchases = Purchase::dates($from_date, $to_date)
            ->with('details')
            ->get();

        foreach ($purchases as $purchase) {
            $amount_metas = $purchase->details->sum('qty');
            $total_purchase += $amount_metas;
            $stock_graph_purchase[$purchase->date] = $amount_metas + $stock_graph_purchase[$purchase->date];
        }

        $total_production = 0;
        $productions = Production::dates($from_date, $to_date)
            ->get();

        foreach ($productions as $production) {
            $amount_metas = $production->qty;
            $total_production += $amount_metas;
            $stock_graph_production[$production->date] = $total_production + $stock_graph_production[$production->date];
        }
        $total_leads = Lead::dates($from_date, $to_date)->count();
        return [
            'total_purchase' => numberFormat($total_purchase),
            'total_production' => numberFormat($total_production),
            'total_leads' => numberFormat($total_leads, 0),
            'total_users' => numberFormat(User::count(), 0),
            'stock_graph' => [
                'labels' => $stock_graph_labels,
                'series' => [
                    array_values($stock_graph_production),
                    array_values($stock_graph_purchase),
                ],
            ]
        ];
    }

}
