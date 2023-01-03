<?php

namespace App\Http\Controllers;

use App\Models\ledgerTransaction;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() && $request->table) {
            $records =ledgerTransaction::all();
            foreach ($records as $r)
            {

            }

            return DataTables::of($records)
                ->rawColumns(['actions'])
                ->setTotalRecords($records->count())
                ->make(true);
        }
        return view('ledger.index', [
            'title' => 'My Ledger',
        ]);
    }

}
