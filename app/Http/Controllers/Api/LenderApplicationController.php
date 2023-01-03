<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    public function newApplications(Request $request)
    {
        $request['query_type'] = 'new';
        return response()->json([
            'error' => 'no',
            'message' => 'success',
            'record' => $this->getRecords($request)
        ]);
    }

    public function myApplications(Request $request)
    {
        $request['query_type'] = 'my';
        return response()->json([
            'error' => 'no',
            'message' => 'success',
            'record' => $this->getRecords($request)
        ]);
    }

    public function getRecords($request)
    {
        $lender_id = $request->lender_id;

        if ($request->query_type == 'new') {
            $records = UserApplication::where(function ($q) {
                $q->where('lender_id', '')
                    ->orWhereNULL('lender_id');
            });
        } elseif ($request->query_type == 'my') {
            $records = UserApplication::where('lender_id', $lender_id);
        }

        $records = $records->with('user')
            ->where('status', '!=', 'Cancelled')
            ->latest()
            ->get();

        $new_records = [];

        foreach ($records as $record) {
            if (!$record->cancelledBy->where('lender_id', $lender_id)->first()) {
                $new_records[] = $record;
            }
        }

        return $new_records;
    }

    public function update(Request $request)
    {
        $error = 'no';
        $message = 'Saved';
        $model_id = $request->application_id;
        $record = UserApplication::find($model_id);
        if ($record->status == 'Pending') {

            $status = $request->status;
            $lender = User::find($request->lender_id);

            if ($status == 'Cancelled') {
                UserApplicationLender::create([
                    'user_application_id' => $model_id,
                    'lender_id' => $lender->id,
                ]);
                if ($record->cancelledBy->count() >= 5) {
                    Mail::to($record->user->email)->send(new UserApplicationCancelMail($record));
                    $record->status = $status;
                    $record->save();
                    $record->user->update([
                        'user_last_app_cancelled_date' => date('Y-m-d'),
                    ]);
                }
            }

            if ($status == 'Approved') {
                $lender_loan_amount = $request->lender_loan_amount;
                if ($lender->balance < $lender_loan_amount) {
                    return response()->json([
                        'error' => 'yes',
                        'message' => 'Your current balance is low',
                        'record' => null
                    ]);
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
                Mail::to($record->user->email)->send(new UserApplicationApprovalMail($record));
            }
        } else {
            $error = 'yes';
            $message = 'Application is Already Approved';
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
            'record' => $record
        ]);
    }


}
