<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Package;
use App\Models\TestTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VendorBill;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class TestTransactionController extends Controller
{
    public $VIEW = 'test-transactions';
    public $TITLE = 'Test Transactions';
    public $URL = 'test-transactions';
    public $SRC = 'images/test-transactions/';

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
        $user = auth()->user();
        $records = TestTransaction::where(function ($q) use ($user) {
            if (!$user->hasRole('admin')) {
                $q->where('receiver_id', $user->id)
                    ->orWhere('sender_id', $user->id);
            }
        })
            ->latest()
            ->get();
        return DataTables::of($records)
            ->editColumn('created_at', function ($record) {
                return $record->createdAt();
            })
            ->editColumn('amount', function ($record) {
                return $record->amount();
            })
            ->addColumn('actions', function ($record) {
                return '';
            })
            ->rawColumns(['actions', 'image'])
            ->setTotalRecords($records->count())
            ->make(true);
    }

    public function create()
    {
        return view($this->VIEW . '.create', [
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $user = auth()->user();
        $amount = $request->amount;
        $type = $request->type;

        if ($type == 'add') {
            TestTransaction::create([
                'amount' => $amount,
                'receiver_email' => $user->email,
                'receiver_id' => $user->id,
                'remarks' => $request->remarks,
                'type' => $type
            ]);

            return redirect($this->URL)
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => $this->TITLE . ' is added',
                        'type' => 'success',
                    ]
                ]);

        }

        $receiver = User::where('email', $request->receiver_email)->first();

        if (!$receiver) {
            return back()
                ->with('message', $request->receiver_email . ' does not exist.')
                ->withInput();
        }

        TestTransaction::create([
            'amount' => $amount,
            'receiver_email' => $receiver->email,
            'receiver_id' => $receiver->id,
            'sender_id' => $user->id,
            'sender_email' => $user->email,
            'remarks' => $request->remarks,
            'type' => $type
        ]);

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
//        return view($this->VIEW . '.show', [
//            'record' => Lead::with('details.product')->findOrFail($id),
//        ]);
    }

    public function edit($id)
    {

    }

    public function update(Request $request, $id)
    {

    }

    public function destroy($id)
    {

    }

}
