<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\UserApplicationAckMail;
use App\Models\Package;
use App\Models\User;
use App\Models\UserApplication;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Mail;
use Illuminate\Support\Facades\Validator;


class UserApplicationController extends Controller
{

    public function index(Request $request)
    {
        $user_id = $request->user_id;
        return response()->json([
            'error' => 'no',
            'message' => 'success',
            'record' => UserApplication::where('user_id', $user_id)->with('lender')->get()->toArray()
        ]);
    }

    public function store(Request $request)
    {
        $user_id = $request->user_id;
        $user = User::find($user_id);

        if ($user->status != 'approved') {
            return response()->json([
                'error' => 'yes',
                'message' => 'Your account is under review',
            ]);
        }

        $data = $request->except('status', 'lender_id');

        $data['user_id'] = $user->id;
        $data['status'] = 'Pending';
        $record = UserApplication::create($data);
        $record->public_id = publicId($record->id);
        $record->save();

        Mail::to($user->email)->send(new UserApplicationAckMail($record));

        return response()->json([
            'error' => 'no',
            'message' => 'success',
            'record' => $record
        ]);
    }
    public function getApplications()
    {
        $records=UserApplication::where('status','pending')->with('user')->get();
        return response()->json(['records'=>$records]);
    }


}
