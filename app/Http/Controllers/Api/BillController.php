<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;

class BillController extends Controller
{
    public $SRC = 'images/bills/';

    public function index(Request $request)
    {
        $user_id = $request->user_id;
        return response()->json([
            'error' => 'no',
            'message' => 'success',
            'record' => Bill::where('user_id', $user_id)
                ->with('category.vendor')
                ->latest()
                ->get()
        ]);
    }

    public function store(Request $request)
    {
        $user = User::find($request->user_id);

        if ($user->status != 'approved') {
            return response()->json([
                'error' => 'yes',
                'message' => 'Your account is under review',
            ]);
        }

        if (($user->balance + $user->my_lender_balance - $user->pay_to_lender) <= 0) {
            return response()->json([
                'error' => 'yes',
                'message' => 'Sorry you have no balance',
            ]);
        }

        $amount = $request->amount;
        $ongoing_bills = $user->bills->where('bill_duration', 'ongoing')->sum('amount');

        if (($user->balance + $user->my_lender_balance - $user->pay_to_lender - $ongoing_bills) < $amount) {
            return response()->json([
                'error' => 'yes',
                'message' => 'Sorry you have low balance',
            ]);
        }

        $data = $request->except('file');
        $data['user_id'] = $user->id;
        $data['status'] = 'active';

        $file = $request->file('file');
        if ($file) {
            $data['file'] = saveImage($file, $this->SRC);
        }

        Bill::create($data);

        $email = $user->email;

        Mail::send('emails.create-bill', [], function ($message) use ($request, $email) {
            $message->to($email)
                ->subject('Your Bill Has Been Added Successfully');
        });

        return response()->json([
            'error' => 'no',
            'message' => 'Saved',
        ]);

    }

    public function destroy($id)
    {
        abort(404);
        $record = Bill::findOrFail($id);

        if ($record->user_id != auth()->id()) {
            abort(404);
        }

        $record->delete();

        return response()->json([
            'error' => 'yes',
            'message' => 'Success',
        ]);

    }

}
