<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Package;
use App\Models\VendorBill;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PackageController extends Controller
{
    public $VIEW = 'packages';
    public $TITLE = 'Packages';
    public $URL = 'packages';
    public $SRC = 'images/packages/';

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
        $records = Package::with(['users'])->get();
        return DataTables::of($records)
            ->addColumn('actions', function ($record) {
                $url = $this->URL;
                $delete_url = $url . '/' . $record->id;
                $actions = "<a href='$url/$record->id/edit' title='Edit Record' class='btn btn-circle btn-success btn-xs mb-5' ><i class='fa fa-edit'></i></a>   ";
                $actions .= "<a href='javascript:' title='Delete Record' class='btn btn-circle btn-danger btn-xs mb-5 delete-btn' data-href='$delete_url'><i class='fa fa-trash'></i></a>";
                return $actions;
            })
            ->addColumn('users', function ($record) {
                return $record->users->count();
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
        Package::create($data);
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
        $record = Package::findOrFail($id);
        return view($this->VIEW . '.edit', [
            'record' => $record,
        ]);
    }

    public function update(Request $request, $id)
    {
        $record = Package::findOrFail($id);
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

        $record = Package::findOrFail($id);
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
