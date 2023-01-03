<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\VendorBill;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AdminBillController extends Controller
{
    public $VIEW = 'admin.bills.';
    public $TITLE = 'Bills';
    public $URL = 'admin/bills';
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
        return view($this->VIEW . 'index', [
            'records' => Bill::where('user_id', auth()->id())
                ->with('category')
                ->latest()
                ->paginate(9)
        ]);
    }

    public function getRecords($request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $records = Bill::
        when($from_date, function ($q) use ($from_date) {
            $q->whereDate('created_at', '>=', $from_date);
        })
            ->when($to_date, function ($q) use ($to_date) {
                $q->whereDate('created_at', '<=', $to_date);
            })
            ->with('category.vendor', 'user')
            ->get();

        return DataTables::of($records)
            ->addColumn('actions', function ($record) {
                return view($this->VIEW . 'actions', [
                    'record' => $record
                ])
                    ->render();
            })
            ->addColumn('file', function ($record) {
                if ($record->file) {
                    return "<a target='_blank' href=" . $record->file() . ">View</a>";
                }
            })
            ->rawColumns(['actions', 'file'])
            ->setTotalRecords($records->count())
            ->make(true);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $record = Bill::findOrFail($id);
        return view($this->VIEW . 'edit', [
            'record' => $record,
        ]);
    }

    public function update(Request $request, $id)
    {
        $record = Bill::findOrFail($id);
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
        //
    }

}
