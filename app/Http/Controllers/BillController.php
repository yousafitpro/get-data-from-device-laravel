<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\mypayee;
use App\Models\PaidBill;
use App\Models\provider;
use App\Models\sharedBill;
use App\Models\sharedBillMember;
use App\Models\User;
use App\Models\VendorBill;
use App\Notifications\billCanceled;
use App\Notifications\billPaymentStartedUsingWallet;
use App\Notifications\billProcessCompleted;
use App\Notifications\newBillAdded;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use Mail;

class BillController extends Controller
{
    public $VIEW = 'bills';
    public $TITLE = 'Bill';
    public $URL = 'bills';
    public $SRC = 'images/bills/';

    public function __construct()
    {
        view()->share([
            'title' => $this->TITLE,
            'url' => url($this->URL),
        ]);
    }
    public function cancel_bill($id)
    {
      $bill=Bill::where('user_id',auth()->id())->first();
      $bill->deleted_at=today_date();
      $bill->save();
        $tempData['bill']=$bill;
        $tempData['user']=auth()->user();
        AlertController::create([
            'message'=>"This Notification is to confirm bill cancellation you initiated on ".$bill->due_date.".
 Please note, bill payment has been cancelled and no further action is required",
            'title'=>"Bill Cancelled",
            'type'=>'network',
            'receiver'=>auth()->user()->id,
            'sender'=>auth()->user()->id
        ]);
        Notification::route('mail', auth()->user()->email)->notify(new billCanceled($tempData));
       if (Request::capture()->expectsJson())
       {
           return response()->json(['message'=>'Bill Cancelled Successfully']);
       }
        return redirect()->back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Bill Cancelled Successfully',
                    'type' => 'success',
                ]
            ]);
    }
    public function shareCreate()
    {
        $vendors=provider::where('deleted_at',null)->where('user_id',0)->get();
        return view('bills.share-create',['vendors'=>$vendors]);
    }

    public function index(Request $request)
    {
        if ($request->ajax() && $request->table) {
            return $this->getRecords($request);
        }
        return view($this->VIEW . '.index', [
            'records' => Bill::where('user_id', auth()->id())
                ->with('category')
                ->latest()
                ->paginate(9)
        ]);
    }
   public function list(Request $request)
   {
       $records = Bill::where('user_id', auth()->id())
           ->with('category.vendor')
           ->get();

       return response()->json(['list'=>$records]);
   }
    public function paid_list(Request $request)
    {
        $records = PaidBill::where('user_id', auth()->id())
            ->get();

        return response()->json(['list'=>$records]);
    }
    public function getRecords($request)
    {

        $records = Bill::where('user_id', auth()->id())
            ->with('category.vendor')
            ->get();

        return DataTables::of($records)
            ->addColumn('actions', function ($record) {
                $url = $this->URL;
                $delete_url = $url . '/' . $record->id;
                $actions = "<a href='$url/$record->id/edit' title='Edit Record' class='btn btn-circle btn-success btn-xs mb-5' ><i class='fa fa-edit'></i></a>   ";
                $actions .= "<a href='javascript:' title='Delete Record' class='btn btn-circle btn-danger btn-xs mb-5 delete-btn' data-href='$delete_url'><i class='fa fa-trash'></i></a>";
                return $actions;
            })
            ->rawColumns(['actions', 'image'])
            ->setTotalRecords($records->count())
            ->make(true);
    }

    public function create()
    {
        $user = auth()->user();
//        if ($user->status != 'approved') {
//            return back()
//                ->with([
//                    'toast' => [
//                        'heading' => 'Message',
//                        'message' => 'Your account is under review',
//                        'type' => 'error',
//                    ]
//                ]);
//        }

//        if (($user->balance + $user->my_lender_balance - $user->pay_to_lender) <= 0) {
//            return back()
//                ->with([
//                    'toast' => [
//                        'heading' => 'Message',
//                        'message' => 'Sorry you have no balance',
//                        'type' => 'error',
//                    ]
//                ]);
//        }
$vendors=provider::where('deleted_at',null)->where('user_id',0)->get();
        return view($this->VIEW . '.create', [
            'vendors' => $vendors,
        ]);
    }

    public function store(Request $request)
    {

       $now=Carbon::now()->toDateString();

        $request->validate([
            'effective_from'=>'required|after_or_equal:'.today_date(),
            'amount'=>'required|numeric|gt:0|max:'.ENVController::$maxBillAmountLimit
        ],[
            'effective_from.required'=>"Date is Required",
            'effective_from.after_or_equal'=>"Date must be equal or greater than today's date",
            'amount.required'=>"Amount is Required to Submit Payment",
            'amount.gt'=>"Amount should be Greater than zero"
        ]);

//        dd("ok");asdasd
            $user = auth()->user();
//        if ($user->status != 'approved') {
//            if (Request::capture()->expectsJson())
//            {
//                return response()->json(['message'=>"Your account is under review"]);
//            }
//            return back()
//                ->with([
//                    'toast' => [
//                        'heading' => 'Message',
//                        'message' => 'Your account is under review',
//                        'type' => 'error',
//                    ]
//                ]);
//        }

        $amount = $request->amount;
        $ongoing_bills = $user->bills->where('bill_duration', 'ongoing')->sum('amount');

//        if (($user->balance + $user->my_lender_balance - $user->pay_to_lender - $ongoing_bills) < $amount) {
//
//            if (Request::capture()->expectsJson())
//            {
//                return response()->json(['message'=>"Sorry you have low balance"]);
//            }
//            return back()
//                ->with([
//                    'toast' => [
//                        'heading' => 'Message',
//                        'message' => 'Sorry you have low balance',
//                        'type' => 'error',
//                    ]
//                ]);
//        }

        $user = auth()->user();
        $data = $request->except('file','token');
        if (!$request->has('effective_from'))
        {
            $data['due_date']=$request->effective_from;
        }
        $data['user_id'] = $user->id;
        $data['status'] = 'active';
        $data['amount']=cutNum($request->amount);
        $file = $request->file('file');
        if ($file) {
            $data['file'] = saveImage($file, $this->SRC);
        }
        $data['due_date']=$data['effective_from'];

        $bill=Bill::create($data);
        $tempData['user']=User::find(auth()->user()->id);
        $tempData['bill']=$bill;

        if ($data['effective_from']==today_date())
        {

                AutoJobBillPayController::payBillNow($bill,User::find(auth()->user()->id),today_date());

                AutoJobBillPayController::updateLastBillDetails($bill,today_date());
        }
        else{

                Notification::send(User::where('id', $user->id)->get(), new newBillAdded($tempData));


        }

//        Mail::send('emails.create-bill', [], function ($message) use ($request, $email) {
//            $message->to($email)
//                ->subject('Your Bill Has Been Added Successfully');
//        });
     if (Request::capture()->expectsJson())
     {
         return response()->json(['message'=>"Payment Successfully Sent"]);
     }
     return redirect(url('my-blls/tabs?tab=Payees'))->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Bill Successfully Created',
                    'type' => 'success',
                ]
            ]);
//        return redirect($this->URL)
//            ->with([
//                'toast' => [
//                    'heading' => 'Message',
//                    'message' => $this->TITLE . ' is created',
//                    'type' => 'success',
//                ]
//            ]);
    }

    public function show($id)
    {
//        return view($this->VIEW . '.show', [
//            'record' => Lead::with('details.product')->findOrFail($id),
//        ]);
    }

    public function edit($id)
    {
        abort(404);

        $record = Bill::findOrFail($id);
        if ($record->user_id != auth()->id()) {
            abort(404);
        }
        return view($this->VIEW . '.edit', [
            'record' => $record,
            'vendors' => VendorBill::with('categories')
                ->orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        abort(404);

        $record = Bill::findOrFail($id);
        if ($record->user_id != auth()->id()) {
            abort(404);
        }
        $data = $request->except('file');
        $record->update($data);
        return redirect($this->URL)
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => $this->TITLE . ' is updated',
                    'type' => 'success',
                ]
            ]);
    }

    public function destroy($id)
    {

        $record = Bill::findOrFail($id);

        if ($record->user_id != auth()->id()) {
            abort(404);
        }

        $record->delete();
        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>"Bill Deleted Successfully"]);
        }
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Bill Deleted Successfully',
                    'type' => 'success',
                ]
            ]);
    }
    public function myBills()
    {

        $data['list']=sharedBill::where(['deleted_at'=>null,'status'=>'Pre-Complete'])->get();
        $data['mypayees']=my_own_payees(auth()->user()->id);
        foreach ($data['mypayees'] as $p)
        {
             $p->paidBills=PaidBill::where('status','amount-received')->where('payee_id',$p->id)->where('user_id',auth()->user()->id)->with('payee')->get();
             $p->OneTimeBills=Bill::where('frequency','once')->where('number_of_paids',null)->where('payee_id',$p->id)->where('deleted_at',null)->where('user_id',auth()->user()->id)->with('payee')->get();

        }
        foreach ($data['list'] as $i)
        {
            $i->payee=mypayee::find($i->mypayee_id);
            $i->count=sharedBillMember::where('shared_bill_id',$i->id)->get()->count();
            $i->members=sharedBillMember::where('shared_bill_id',$i->id)->get();
        }


        $data['paidBills']=PaidBill::where('status','amount-received')->where('user_id',auth()->user()->id)->get();
        $data['ReOccurringBills']=Bill::where('frequency','!=','once')->where('deleted_at',null)->where('user_id',auth()->user()->id)->get();
//        $data['OneTimeBills']=Bill::where('frequency','==','once')->where('deleted_at',null)->where('number_of_paids',null)->where('user_id',auth()->user()->id)->get();

        foreach ($data['paidBills'] as $b)
        {
            $b->bill=Bill::find($b->bill_id);
            $b->payee=mypayee::find($b->payee_id);
//            $b->OneTimeBills=Bill::where('frequency','==','once')->where('deleted_at',null)->where('number_of_paids',null)->where('user_id',auth()->user()->id)->get();
        }
        foreach ($data['ReOccurringBills'] as $b)
        {

            $b->payee=mypayee::find($b->payee_id);
        }


        //        $data['payees']=PaidBill::where('user_id',auth()->user()->id)->where('frequency','!=','once')->get();

        if (Request::capture()->expectsJson())
        {
            return response()->json($data);
        }
        return view('mybills.tabs',$data);
    }

    public function allBills(Request $request)
    {
        $q=Bill::withTrashed();

        // status Check
        if ($request->has('status') && $request->status=="deleted")
        {
            $q=$q->where('deleted_at','!=',null);
            Session::put('all_bills_status','deleted');

        }
        else if ($request->has('status') && $request->status=="processed")
        {
            $q=$q->where('number_of_bills',1)->where('frequency','once');
            Session::put('all_bills_status','processed');

        }
        else
        {
            Session::put('all_bills_status','all');
        }
         // date check

        if ($request->has('date') and $request->date!=null)
        {
            $q=$q->whereDate('due_date',$request->date);
            Session::put('all_bills_date',$request->date);
        }
        else{
            Session::put('all_bills_date',null);
        }


         $data['bills']=$q->get();

        return view('bills.allBills',$data);
    }

    public function allPaidBills(Request $request)
    {
        $q=PaidBill::withTrashed();
      // payment method
        if ($request->has('payment_method'))
        {
            $q=$q->where('payment_method',$request->payment_method);
            Session::put('paid_bills_payment_method',$request->payment_method);

        }
        else
        {
            Session::put('paid_bills_payment_method','all');
        }
        // status Check
        if ($request->has('status') && $request->status=="sent")
        {
            $q=$q->where('is_sent_to_pay','sent');
            Session::put('paid_bills_status','sent');

        }
        else if ($request->has('status') && $request->status=="pending")
        {
            $q=$q->where('is_sent_to_pay','Pending');
            Session::put('paid_bills_status','pending');

        }
        else if ($request->has('status') && $request->status=="deleted")
        {
            $q=$q->where('deleted_at','!=',null);
            Session::put('paid_bills_status','deleted');

        }
        else
        {
            Session::put('paid_bills_status','all');
        }
        // date check

        if ($request->has('date') and $request->date!=null)
        {
            $q=$q->whereDate('date',$request->date);
            Session::put('paid_bills_date',$request->date);
        }
        else{
            Session::put('paid_bills_date',null);
        }


        $data['bills']=$q->get();

        return view('bills.paidBills',$data);
    }
}
