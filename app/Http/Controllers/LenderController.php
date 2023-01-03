<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\User;
use App\Models\VendorBill;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class LenderController extends Controller
{
    public $VIEW = 'lenders';
    public $TITLE = 'Lenders';
    public $URL = 'lenders';
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
        if ($request->ajax() && $request->table) {
            return $this->getRecords($request);
        }
        return view($this->VIEW . '.index');
    }

    public function getRecords($request)
    {
        $records = User::role('lender')
            ->notDeleted()
            ->get();
        return DataTables::of($records)
            ->addColumn('actions', function ($record) {
                $url = $this->URL;
                $delete_url = $url . '/' . $record->id;
                $actions = "<a href='$url/$record->id/edit' title='Edit Record' class='btn btn-circle btn-success btn-xs mb-5' ><i class='fa fa-edit'></i></a>   ";
                $actions .= "<a href='javascript:'  title='Delete Record' class='btn btn-circle btn-danger btn-xs mb-5 delete-btn' data-href='$delete_url'><i class='fa fa-trash'></i></a>";
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
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        $data = $request->all();
        $data['email_verified_at'] = now();
        $data['password'] = bcrypt($request->password);
        $user = User::create($data);
        $role = Role::find(2);
        $user->assignRole($role->name);

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
        $record = User::findOrFail($id);
        return view($this->VIEW . '.edit', [
            'record' => $record,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,'.$id,
        ]);

        $record = User::findOrFail($id);
        $data = $request->except('password');
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
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
        $record = User::findOrFail($id);
        $record->is_deleted = 1;
        $record->save();

        return [
            'toast' => [
                'heading' => 'Message',
                'message' => $this->TITLE . ' is deleted',
                'type' => 'success',
            ]
        ];
    }
}
