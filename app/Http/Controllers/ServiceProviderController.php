<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\payee;
use App\Models\provider;
use App\Models\User;
use App\Models\VendorBill;
use App\Models\VendorBillCategory;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class ServiceProviderController extends Controller
{
    public $VIEW = 'service-providers';
    public $TITLE = 'Service Providers';
    public $URL = 'admin/service-providers';
    public $SRC = 'images/avatar/';

    public function __construct()
    {
        view()->share([
            'title' => $this->TITLE,
            'url' => url($this->URL),
        ]);
    }

    public function index(Request $request)
    {
        $list=payee::where('user_id',auth()->user()->id)
            ->get();

        return view($this->VIEW . '.index', [
            'records' => $list
        ]);
    }

    public function getRecords($request)
    {
        $records = payee::where('user_id',0)
            ->where("deleted_at",'!=',null)
            ->get();
        return DataTables::of($records)
            ->addColumn('actions', function ($record) {
                $url = $this->URL;
                $delete_url = $url . '/' . $record->id;
                $actions = "<a href='$url/$record->id/edit' title='Edit Record' class='btn btn-circle btn-success btn-xs mb-5' ><i class='fa fa-edit'></i></a>   ";
                return $actions;
            })
            ->rawColumns(['actions', 'image'])
            ->setTotalRecords($records->count())
            ->make(true);
    }

    public function create()
    {

        return view($this->VIEW . '.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'digit_limit'=>'nullable|integer'
        ]);

            payee::create([
                'user_id'=>auth()->user()->id,
                'type'=>"Business",
                'company_name' => $request->title,
                'digit_limit' => $request->digit_limit
            ]);

        return redirect($this->URL)
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Provider is created',
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

    public function edit(Request $request, $id)
    {

            $record = payee::findOrFail($id);

        return view($this->VIEW . '.edit', [
            'record' => $record,
            'vendors' => VendorBill::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'digit_limit'=>'nullable|integer'
        ]);


          payee::findOrFail($id)
                ->update([
                    'company_name' => $request->title,
                    'digit_limit' => $request->digit_limit
                ]);


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
        $record = User::findOrFail($id);

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
