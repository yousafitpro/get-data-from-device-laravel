<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\ServiceSuggestionMail;
use App\Models\aptpaysendpayment;
use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\Business;
use App\Models\contact;
use App\Models\etransfer_transaction;
use App\Models\Lead;
use App\Models\ledgerTransaction;
use App\Models\LocBankAccount;
use App\Models\Merchant\merchantOffers;
use App\Models\mypayee;
use App\Models\Package;
use App\Models\PaidBill;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\sendandrequestfund;
use App\Models\sharedBill;
use App\Models\sharedBillMember;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Mail;

class DashboardController extends Controller
{

    public function set_reoccurring_billView()
    {
        return view('mybills.setReoccuring');
    }
    public function payment_requests(Request $request)
    {

        $data['requests']=[];
        if (contact::where('username',auth()->user()->email)->exists())
        {
            $users=contact::where('username',auth()->user()->email)->pluck('id');


            $data['requests']=sharedBillMember::whereIn('contact_id',$users->all())->where('status','Pending')->get();

          foreach ($data['requests'] as $r)
          {
              $r->bill=sharedBill::find($r->shared_bill_id);

              $r->payee=mypayee::find($r->bill->mypayee_id);
              $r->contact=contact::find($r->contact_id);
              $r->creator=User::find($r->user_id);
          }
        }

        $data['users']=contact::where(['deleted_at'=>null,'creator_id'=>auth()->user()->id])->get();
        $data['credit_accounts']=my_credits();
        if (Request::capture()->expectsJson())
        {
            return response()->json($data);
        }
        $data['history']=sendandrequestfund::where(['user_id'=>auth()->id(),'direction'=>'request'])->get();
        return view('dashboard.requests',$data);
    }
    public function myAccounts()
    {
        $data['loc_accounts']=LocBankAccount::where('user_id',auth()->user()->id)->get();
        $data['main_accounts']=BankAccount::where('user_id',auth()->user()->id)->get();

        return view('dashboard.my-accounts')->with(['data'=>$data]);
    }
    public function index()
    {

        $mont=Carbon::now();
        $start=$mont->startOfMonth()->toDateString();
        $end=$mont->endOfMonth()->toDateString();
        $start_of_year=$mont->startOfYear()->toDateString();
        $end_of_year=$mont->endOfYear()->toDateString();
        if(auth()->user()->hasRole('lender'))
        {
            auth()->logout();
            return redirect()->back();
        }
         $mot=merchantOffers::Myown()->get()->count();
         $moa=merchantOffers::Myown()->where('status',"APPROVED")->get()->count();
         if ($mot==0)
         {
             $mot=1;
         }
        $data['merchant_offers_success_percentage']=$moa/$mot*100;
        // monthly refunds
        $monthlyData =aptpaysendpayment::Myown()
            ->whereBetween('created_at',[$start_of_year,$end_of_year])
            ->where('status','APPROVED')
            ->where('s_type','Refund')
            ->select(DB::raw('sum(amount) as `amount`'),DB::raw('MONTH(created_at) date'))
            ->groupby('date')
            ->orderBy('date', 'ASC')
            ->get();
        $data['monthlyRefundData']=$monthlyData->pluck('amount')->toArray();
        $data['monthlyDataNames']=$monthlyData->pluck('date')->toArray();
        $refundValues=[0,0,0,0,0,0,0,0,0,0,0,0];
        foreach ($data['monthlyDataNames'] as $index =>$v)
        {
            $refundValues[$v-1]=$data['monthlyRefundData'][$index];
        }
        $data['monthlyRefundData']=$refundValues;
///asdasdas
        // monthly data
        $monthlyData =merchantOffers::Myown()
            ->whereBetween('created_at',[$start_of_year,$end_of_year])
            ->where('status','APPROVED')
            ->select(DB::raw('sum(amount) as `amount`'),DB::raw('MONTH(created_at) date'))
            ->groupby('date')
            ->orderBy('date', 'ASC')
            ->get();
        $data['monthlySalesData']=$monthlyData->pluck('amount')->toArray();
        $data['monthlyDataNames']=$monthlyData->pluck('date')->toArray();
        $refundValues=[0,0,0,0,0,0,0,0,0,0,0,0];
        foreach ($data['monthlyDataNames'] as $index =>$v)
        {
            $refundValues[$v-1]=$data['monthlySalesData'][$index];
        }
        $data['monthlySalesData']=$refundValues;



        $lender_balance= (int)auth()->user()->my_lender_balance;
        $barChatData['Yvalues']=[0,30,0];
        $data['merchant_offers_amount_sum_today']=merchantOffers::Myown()->whereBetween('created_at',[$start,$end])->where('status','APPROVED')->get()->sum("amount");
        $data['merchant_available_balance']=merchantOffers::Myown()->where('status','APPROVED')->get()->sum("amount");
        $data['linkPaymentsSum']=merchantOffers::Myown()->where('status','APPROVED')->whereBetween('created_at',[$start,$end])->where('type','offer')->get()->sum("amount");
        $data['offerPaymentsSum']=merchantOffers::Myown()->where('status','APPROVED')->whereBetween('created_at',[$start,$end])->where('type','link')->get()->sum("amount");
        $data['fundsClearedSum']=merchantOffers::Myown()->where('status','APPROVED')->whereBetween('created_at',[$start,$end])->where('is_funds_cleared','Cleared')->get()->sum("amount");
        $data['total_withdraw']=aptpaysendpayment::Myown()->where('s_type','Wallet')->where('status',"APPROVED")->get()->sum('amount');
        $data['total_refund']=aptpaysendpayment::Myown()->where('s_type','Refund')->where('status',"APPROVED")->get()->sum('amount');
        $data['loc_accounts']=LocBankAccount::where('deleted_at',null)->where('user_id',auth()->user()->id)->get();
        $data['main_accounts']=BankAccount::where('deleted_at',null)->where('user_id',auth()->user()->id)->get();
        $data['ReOccurringBills']=Bill::where('frequency','!=','once')->where('deleted_at',null)->where('user_id',auth()->user()->id)->get();
    $rc=0;
     foreach ($data['ReOccurringBills'] as $b)
     {
         if(($b->number_of_bills==null) ||  ($b->number_of_bills!=null && $b->number_of_bills!=$b->number_of_paids))
         {
             $rc=$rc+1;
         }
     }
        $data['reoccuring_bill_count']=$rc;

       $obills=Bill::where('frequency','once')->whereBetween('created_at',[$start,$end])->where('user_id',auth()->user()->id)->get();
        $obc=0;
        foreach ($obills as $b)
        {
            if (PaidBill::where('bill_id',$b->id)->exists())
            {
                $obc=$obc+1;
            }
        }
        $data['onetime_bill_count']=$obc;
        $data['paid_bill_count']=PaidBill::where('user_id',auth()->user()->id)->whereBetween('created_at',[$start,$end])->where('status','amount-received')->get()->count();

        $data['package']=Package::find(auth()->user()->package_id);
        $data['balance']=auth()->user()->wallet_balance;
        $data['wallet_payments']=PaidBill::where('user_id',auth()->user()->id)->whereBetween('created_at',[$start,$end])->where('status','amount-received')->where('payment_method','wallet')->get()->sum('amount');
        $data['card_payments']=PaidBill::where('user_id',auth()->user()->id)->whereBetween('created_at',[$start,$end])->where('status','amount-received')->where('payment_method','card')->get()->sum('amount');


        $last_five_bills= PaidBill::where('user_id', auth()->id())->where('status','amount-received')->with('vendorBillCategory.vendor')->latest()->take(5)->get();
        foreach ($last_five_bills as $pb)
        {
            $pb->payee=\App\Models\mypayee::find($pb->payee_id);
        }
//
        foreach ($last_five_bills as $b)
        {
            $b->short_date=Carbon::parse($b->created_at)->toDateString();
        }
        $data['last_five_bills']=$last_five_bills;
        if (Request::capture()->expectsJson())
        {
            return response()->json($data);
        }


        return view('dashboard.index')->with(['barChartData'=>$barChatData,'data'=>$data]);
    }

    public function myLender(Request $request)
    {
        $user = auth()->user();
        if ($request->ajax() && $request->table) {
            $records = Transaction::where('receiver_id', $user->id)
                ->where('sender_id', $user->lender_id)
                ->latest()
                ->get();
            return DataTables::of($records)
                ->editColumn('created_at', function ($record) {
                    return $record->createdAt();
                })
                ->editColumn('amount', function ($record) {
                    return $record->amount();
                })
                ->rawColumns(['actions', 'image'])
                ->setTotalRecords($records->count())
                ->make(true);
        }
        return view('dashboard.my-lender', [
        ]);
    }

    public function myUsers(Request $request)
    {
        if ($request->ajax() && $request->table) {
            $records = auth()->user()->myUsers;
            return DataTables::of($records)
                ->rawColumns(['actions', 'image'])
                ->setTotalRecords($records->count())
                ->make(true);
        }
        return view('dashboard.my-users', [
            'title' => 'My Users',
            'url' => url('my-users'),
        ]);
    }

    public function myPaidBills(Request $request)
    {
        if ($request->ajax() && $request->table) {
            $records = PaidBill::where('user_id', auth()->id())->with('vendorBillCategory')->get();
            return DataTables::of($records)
                ->editColumn('created_at', function ($record) {
                    return $record->createdAt();
                })
                ->editColumn('amount', function ($record) {
                    return $record->amount();
                })
                ->rawColumns(['actions', 'image'])
                ->setTotalRecords($records->count())
                ->make(true);
        }
        return view('dashboard.my-paid-bills', [
            'title' => 'My Paid Bills',
            'url' => url('my-paid-bills'),
        ]);
    }
    public function myTransactions(Request $request)
    {
        if ($request->ajax() && $request->table) {
            $records =ledgerTransaction::where('user_id', auth()->id())
                ->where('type','user')
                ->get();
            foreach ($records as $r)
            {

            }

            return DataTables::of($records)
                ->rawColumns(['actions'])
                ->setTotalRecords($records->count())
                ->make(true);
        }

        return view('dashboard.ledger-transactions', [
            'title' => 'Bill Transactions',
        ]);
    }

    public function PaidBills(Request $request)
    {
        if ($request->ajax() && $request->table) {
            $records = PaidBill::latest()
                ->with('vendorBillCategory', 'user', 'bill')
                ->get();
            return DataTables::of($records)
                ->editColumn('created_at', function ($record) {
                    return $record->createdAt();
                })
                ->rawColumns(['actions', 'image'])
                ->setTotalRecords($records->count())
                ->make(true);
        }
        return view('dashboard.paid-bills', [
            'title' => 'Paid Bills',
            'url' => url('paid-bills'),
        ]);
    }

    public function generateDateRange($start_date, $end_date)
    {
        $dates = [];
        $start_date = Carbon::create($start_date);
        $end_date = Carbon::create($end_date);
        for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }
        return $dates;
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

    public function serviceSuggestion(Request $request)
    {
        $email = Business::first()->email;
        Mail::to($email)->send(new ServiceSuggestionMail($request->all()));
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Thanks for writing us',
                    'type' => 'success',
                ]
            ]);
    }

}
