<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\User;
use App\Models\VendorBill;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    public $VIEW = 'users';
    public $TITLE = 'Users';
    public $URL = 'users';
    public $SRC = 'images/avatar/';

    public function __construct()
    {
        view()->share([
            'title' => $this->TITLE,
            'url' => url($this->URL),
        ]);
    }
public function delete_account()
{
    //sadas
    $user=auth()->user();
    $user->status="Deleted";
    $user->save();
    if (Request::capture()->expectsJson())
    {
        return response()->json(['message'=>"Account Successfully Deleted"]);
    }
}
    public function index(Request $request)
    {
        if ($request->ajax() && $request->table) {
            $request['status'] = 'approved';
            return $this->getRecords($request);
        }
        return view($this->VIEW . '.index');
    }

    public function newUsers(Request $request)
    {
        if ($request->ajax() && $request->table) {
            $request['new_users'] = true;
            return $this->getRecords($request);
        }

        return view($this->VIEW . '.index', [
            'url' => url('new-users'),
            'title' => 'New User',
        ]);

    }

    public function bannedUsers(Request $request)
    {
        if ($request->ajax() && $request->table) {
            $request['status'] = 'banned';
            return $this->getRecords($request);
        }
        return view($this->VIEW . '.index', [
            'url' => url('banned-users'),
            'title' => 'Banned User',
        ]);
    }

    public function getRecords($request)
    {
        $new_users = $request['new_users'];
        $status = $request['status'];
        $records = User::role('user')
            ->notDeleted()
            ->when($new_users, function ($q) {
                $q->where(function ($q) {
                    $q->where('status', null)
                        ->orWhere('status', Null)
                        ->orWhere('status', '');
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->with('package')
            ->get();

        return DataTables::of($records)
            ->editColumn('lender_email', function ($record) {
                return $record->lenderEmail();
            })
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
        //
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
            'lenders' => User::role('lender')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
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
        $record->email .= '//deleted';
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
