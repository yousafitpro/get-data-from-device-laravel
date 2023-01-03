<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\PaidBill;
use App\Models\User;
use App\Models\VendorBill;
use App\Notifications\bill_refunded;
use App\Notifications\billChargedFromCardInsteadWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Yajra\DataTables\DataTables;

class PaidBillController extends Controller
{
    public $VIEW = 'bills';
    public $TITLE = 'Bill Payment History';
    public $URL = 'bills';
    public $SRC = 'images/bills/';

    public function __construct()
    {
        view()->share([
            'title' => $this->TITLE,
            'url' => url($this->URL),
        ]);
    }

    public function index(Request $request)
    {
        if ($request->ajax() && $request->table) {
            return $this->getRecords($request);
        }
        return view($this->VIEW . '.index');
    }

    public function getRecords($request)
    {
        $records = PaidBill::where('user_id', auth()->id())
//            ->with('category')
            ->get();
        return DataTables::of($records)
            ->editColumn('amount', function ($record) {
                return $record->amount();
            })
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
        return view($this->VIEW . '.create', [
            'vendors' => VendorBill::with('categories')
                ->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        Bill::create($data);
        return redirect($this->URL)
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => $this->TITLE . ' is created',
                    'type' => 'success',
                ]
            ]);
    }
  public function refund_bill(Request $request)
  {

      $request->validate([
          'reason'=>'required'
      ]);

      if (PaidBill::where('id',$request->bill_id)->where('status',"amount-received")->where('is_sent_to_pay','!=','Refunded')->exists())
      {
          $pb=PaidBill::find($request->bill_id);

          $amount=$pb->amount;
//          if ($pb->payment_method=="card")
//          {
//              $amount=$amount-$pb->p_c_m_c_amount;
//
//          }


          if (($pb->type=="self-added" && $pb->status=="amount-received" && $pb->is_sent_to_pay!="Processed") && ($pb->is_sent_to_pay=="Pending" || $pb->is_sent_to_pay=="Not-Paid"))
          {

              $amount=$amount-$pb->p_s_m_c_amount;
          }

          if (add_balance_to_wallet($pb->bill->user->id,$amount,'Bill Amount Refunded!'))
          {

              $pb->is_sent_to_pay='Refunded';
              $pb->refund_reason=$request->reason;
              $pb->save();
              $ndata['bill']=$pb;
              $ndata['amount']=$amount;
            //  Notification::send(User::where('id',$pb->bill->user->id)->get(),new bill_refunded($ndata));

              return redirect()->back()
                  ->with([
                      'toast' => [
                          'heading' => 'Message',
                          'message' => "Bill Successfully Refunded",
                          'type' => 'success',
                      ]
                  ]);
          }
      }
      return redirect($this->URL)
          ->with([
              'toast' => [
                  'heading' => 'Message',
                  'message' => 'Sorry! this bill cannot be refunded.',
                  'type' => 'error',
              ]
          ]);




  }
    public function show($id)
    {
//        return view($this->VIEW . '.show', [
//            'record' => Lead::with('details.product')->findOrFail($id),
//        ]);
    }

    public function edit($id)
    {
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
        return [
            'toast' => [
                'heading' => 'Message',
                'message' => $this->TITLE . ' is deleted',
                'type' => 'success',
            ]
        ];
    }

}
