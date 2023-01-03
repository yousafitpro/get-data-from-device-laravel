<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\UserApplication;
use App\Models\VendorBill;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AdminApplicationController extends Controller
{
    public $VIEW = 'admin.applications';
    public $TITLE = 'Applications';
    public $URL = 'admin/applications';
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

    public function getRecords($request)
    {
        $records = UserApplication::
                where('status', $request->status)
            ->with('user','lender')
            ->get();
        return DataTables::of($records)
            ->editColumn('created_at', function ($record) {
                return dateFormat($record->created_at);
            })
            ->addColumn('actions', function ($record) {
                $url = url($this->URL);
                $delete_url = url($url . '/' . $record->id);
                $actions = "<a href='$url/$record->public_id' title='View Record' class='btn btn-circle btn-success btn-xs mb-5' ><i class='fa fa-print'></i></a>   ";
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
        return view($this->VIEW . '.create', [
            'vendors' => VendorBill::with('categories')
                ->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $data = $request->except('status', 'lender_id');
        $data['user_id'] = $user->id;
        $record = UserApplication::create($data);
        $record->public_id = publicId($record->id);
        $record->save();
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
