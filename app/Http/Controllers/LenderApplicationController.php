<?php

namespace App\Http\Controllers;

use App\Mail\UserApplicationApprovalMail;
use App\Mail\UserApplicationCancelMail;
use App\Models\Bill;
use App\Models\User;
use App\Models\UserApplication;
use App\Models\UserApplicationLender;
use App\Models\VendorBill;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Mail;

class LenderApplicationController extends Controller
{
    public $VIEW = 'applications';
    public $TITLE = 'Application';
    public $URL = 'new-applications';
    public $SRC = 'images/applications/';

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
            $request['query_type'] = 'my';
            return $this->getRecords($request);
        }
        return view($this->VIEW . '.my-index', [
            'title' => 'Approved Applications'
        ]);
    }

    public function new(Request $request)
    {
        if ($request->ajax() && $request->table) {
            $request['query_type'] = 'new';
            return $this->getRecords($request);
        }
        return view($this->VIEW . '.new-index');
    }

    public function getRecords($request)
    {
        if ($request->query_type == 'new') {
            $records = UserApplication::where(function ($q) {
                $q->where('lender_id', '')
                    ->orWhereNULL('lender_id');
            });
        } elseif ($request->query_type == 'my') {
            $records = UserApplication::where('lender_id', auth()->id());
        }

        $records = $records->with('user')
            ->where('status', '!=', 'Cancelled')
            ->latest()
            ->get();

        $new_records = [];

        foreach ($records as $record) {
            if (!$record->cancelledBy->where('lender_id', auth()->id())->first()) {
                $new_records[] = $record;
            }
        }

        if ($request->only_records) {
            return [
                'records' => $new_records
            ];
        }

        return DataTables::of($new_records)
            ->editColumn('created_at', function ($record) {
                return dateFormat($record->created_at);
            })
            ->addColumn('actions', function ($record) {
                $url = url('applications');
                $actions = "<a href='$url/$record->public_id' title='View Record' class='btn btn-circle btn-success btn-xs mb-5' ><i class='fa fa-print'></i></a>   ";
                return $actions;
            })
            ->rawColumns(['actions', 'image'])
            ->setTotalRecords(count($new_records))
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
        //
    }

    public function show($id)
    {
        $model_id = modelId($id);
        return view($this->VIEW . '.show', [
            'record' => UserApplication::findOrFail($model_id)
        ]);
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

        $model_id = modelId($id);
        $record = UserApplication::findOrFail($model_id);
        $user=User::find($record->user_id);
        $status = $request->status;
        $lender = auth()->user();

        if ($record->status == 'Pending') {

            if ($status == 'Cancelled') {

                UserApplicationLender::create([
                    'user_application_id' => $model_id,
                    'lender_id' => auth()->id(),
                    'cancel_reason' => $request->lender_comment,
                ]);

                if ($record->cancelledBy->count() >= 5) {
                    Mail::to($record->user->email)->send(new UserApplicationCancelMail($record));
                    $record->status = $status;
                    $record->lender_comment = $request->lender_comment;
                    $record->save();
                    $record->user->update([
                        'user_last_app_cancelled_date' => date('Y-m-d'),
                    ]);
                }

            }

            if ($status == 'Approved') {
                $lender_loan_amount = $request->lender_loan_amount;
                if ($lender->balance < $lender_loan_amount) {
                    abort(422, 'Your current balance is low ' . $lender->balance ?: 0);
                }

                $record->update([
                    'lender_id' => auth()->id(),
                    'status' => $status,
                    'interest_rate' => $request->interest_rate,
                    'lender_loan_amount' => $lender_loan_amount,
                ]);

                $lender->balance -= $lender_loan_amount;
                $lender->save();

                $record->user->update([
                    'interest_rate' => $request->interest_rate,
                    'lender_id' => auth()->id(),
                    'my_lender_balance' => $record->my_lender_balance + $lender_loan_amount
                ]);
                UserApplication::where('user_id',$user->id)->where('id','!=',$record->id)->delete();

//                Mail::to($record->user->email)->send(new UserApplicationApprovalMail($record));
            }
        } else {
            abort(422, $this->TITLE . ' is approved by an other lender');
        }

        return response()
            ->json([
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
