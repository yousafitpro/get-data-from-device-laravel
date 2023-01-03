<?php

namespace App\Http\Controllers;

use App\Mail\UserApplicationAckMail;
use App\Models\applicationoffer;
use App\Models\Bill;
use App\Models\Package;
use App\Models\UserApplication;
use App\Models\VendorBill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Mail;

class ApplicationController extends Controller
{
    public $VIEW = 'applications';
    public $TITLE = 'Credit Applications';
    public $URL = 'applications';
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
            return $this->getRecords($request);
        }
        return view($this->VIEW . '.index');
    }
    public function list(Request $request)
    {
        $records = UserApplication::where('user_id', auth()->id())
            ->get();
        foreach ($records as $r) {
            $r->offers = applicationoffer::where('user_application_id', $r->id)->get()->count();
        }
        return response(['list'=>$records]);
    }
    public function getRecords($request)
    {
        $records = UserApplication::where('user_id', auth()->id())
            ->get();
        foreach ($records as $r)
        {
            $r->offers=applicationoffer::where('user_application_id',$r->id)->get()->count();
        }
        return DataTables::of($records)
            ->editColumn('created_at', function ($record) {
                return dateFormat($record->created_at);
            })
            ->addColumn('actions', function ($record) {
                $url = $this->URL;
                $delete_url = url($url . '/' . $record->id);
                $actions = "<a href='$url/$record->public_id' title='View Record' class='btn btn-circle btn-success btn-xs mb-5' ><i class='fa fa-print'></i></a>   ";
                $actions = $actions."<a href='applicationoffers?application_id=$record->id' title='Offers' class='btn btn-circle btn-success btn-xs mb-5' ><i class='fa fa-users'></i></a>   ";

//                $actions = "<a href='$url/$record->id/edit' title='Edit Record' class='btn btn-circle btn-success btn-xs mb-5' ><i class='fa fa-edit'></i></a>   ";
//                $actions .= "<a href='javascript:' title='Delete Record' class='btn btn-circle btn-danger btn-xs mb-5 delete-btn' data-href='$delete_url'><i class='fa fa-trash'></i></a>";
                return $actions;
            })
            ->rawColumns(['actions', 'image'])
            ->setTotalRecords($records->count())
            ->make(true);
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->status != 'approved') {
            return back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'Your account is under review',
                        'type' => 'error',
                    ]
                ]);
        }

        if ($user->my_lender_balance ) {
            return back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'You already have loan balance in your',
                        'type' => 'error',
                    ]
                ]);
        }

        $user_last_app_cancelled_date = $user->user_last_app_cancelled_date;

        if ($user_last_app_cancelled_date) {
            $last_cancelled_date = Carbon::make($user_last_app_cancelled_date)->addMonths(3)->format('Y-m-d');
            if ($last_cancelled_date >= date('Y-m-d')) {
                return back()
                    ->with([
                        'toast' => [
                            'heading' => 'Message',
                            'message' => 'You can add next application after 3 months',
                            'type' => 'success',
                        ]
                    ]);
            }
        }

        return view($this->VIEW . '.create', [
//            'vendors' => VendorBill::with('categories')
//                ->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->status != 'approved') {
            if (Request::capture()->expectsJson())
            {
                return response()->json(['message'=>"Your account is under review"]);
            }
            return back()
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => 'Your account is under review',
                        'type' => 'error',
                    ]
                ]);
        }
        $data = $request->except('status', 'lender_id','token');
        $data['user_id'] = $user->id;
        $data['status'] = 'Pending';
        $data['package_type'] = Package::find(auth()->user()->package_id)->title;
        $record = UserApplication::create($data);
        $record->public_id = publicId($record->id);
        $record->save();
//        Mail::to($user->email)->send(new UserApplicationAckMail($record));
        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>"Application Successfully Added"]);
        }
        return redirect($this->URL)
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => $this->TITLE . ' is created',
                    'type' => 'success',
                ]
            ]);
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
