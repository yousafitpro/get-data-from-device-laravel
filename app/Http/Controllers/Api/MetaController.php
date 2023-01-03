<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ServiceSuggestionMail;
use App\Models\Business;
use App\Models\Package;
use App\Models\PaidBill;
use App\Models\User;
use App\Models\VendorBill;
use Illuminate\Http\Request;
use Mail;
use Illuminate\Support\Facades\Validator;


class MetaController extends Controller
{

    public function planList()
    {
        return response()->json([
            'error' => 'no',
            'message' => 'success',
            'record' => Package::all('id', 'title')->toArray()
        ]);
    }

    public function userDashboard(Request $request)
    {
        $error = 'no';
        $message = 'success';

        $user_id = $request->user_id;
        $user = User::find($user_id);

        if (!$user) {
            $error = 'yes';
            $message = 'error';
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
            'record' => $user
        ]);
    }

    public function lenderDashboard(Request $request)
    {
        $error = 'no';
        $message = 'success';

        $user_id = $request->lender_id;
        $user = User::find($user_id);

        if (!$user) {
            $error = 'yes';
            $message = 'error';
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
            'record' => $user
        ]);

    }

    public function billVendorList(Request $request)
    {
        $title = $request->title;

        return response()->json([
            'error' => 'no',
            'message' => 'success',
            'record' => VendorBill::when($title, function ($q) use ($title) {
                $q->where('title', 'LIKE', '%' . $title . '%')
                    ->whereHas('categories', function ($q) use ($title) {
                        $q->where('title', 'LIKE', '%' . $title . '%');
                    });
            })
                ->with('categories')
                ->orderBy('title')
                ->get()
        ]);
    }

    public function serviceSuggestion(Request $request)
    {
        $email = Business::first()->email;
        Mail::to($email)->send(new ServiceSuggestionMail($request->all()));

        return response()->json([
            'error' => 'no',
            'message' => 'success',
            'record' => ''
        ]);

    }

    public function userPaidBills(Request $request)
    {
        $error = 'no';
        $message = 'success';
        $user_id = $request->user_id;
        return response()->json([
            'error' => $error,
            'message' => $message,
            'record' => PaidBill::where('user_id', $user_id)
                ->with('vendorBillCategory.vendor', 'bill')
                ->latest()
                ->get()
        ]);
    }

    public function myUsers(Request $request)
    {
        $user = User::find($request->lender_id);

        $records = $user->myUsers;
        return response()->json([
            'error' => 'no',
            'message' => 'success',
            'record' => $records
        ]);

    }

}
