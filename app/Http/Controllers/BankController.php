<?php

namespace App\Http\Controllers;

use App\Models\bank;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\DataTables;

class BankController extends Controller
{
    public $VIEW = 'admin.banks';
    public $TITLE = 'bank';
    public $URL = 'banks';
    public $SRC = 'images/banks/';

    public function __construct()
    {
        view()->share([
            'title' => $this->TITLE,
            'url' => url($this->URL),
        ]);
    }
    public function transfers()
    {
        return view('banking.transfers');
    }

    public function index(Request $request)
    {
        if ($request->ajax() && $request->table) {
            return $this->getRecords($request);
        }
        return view($this->VIEW . '.index', [
            'records' => bank::where('user_id', auth()->id())->where("deleted_at",null)
                ->latest()
                ->paginate(9)
        ]);
    }

    public function getRecords($request)
    {

        $records = bank::where('user_id', auth()->id())->where("deleted_at",null)
            ->get();

        return DataTables::of($records)
            ->addColumn('actions', function ($record) {
                $url = $this->URL;
                $delete_url = $url . '/' . $record->id;
                $actions = "<a href='$url/$record->id' title='Edit Record' class='btn btn-circle btn-success btn-xs mb-5' ><i class='fa fa-eye'></i></a>   ";
                $actions .= "<a href='javascript:' title='Delete Record' class='btn btn-circle btn-danger btn-xs mb-5 delete-btn' data-href='$delete_url'><i class='fa fa-trash'></i></a>";
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


        return view($this->VIEW . '.create');
    }

    public function store(Request $request)
    {

        $bank=new bank();
        if(bank::where("routing_number",$request->number)->exists())
        {
            $bank=bank::where("routing_number",$request->number)->first();
        }
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
        $response =Http::withBasicAuth(config('myconfig.MT.oid'), config('myconfig.MT.key'))
            ->asJson()
            ->get(config('myconfig.MT.url').'/api/validations/routing_numbers',[
                'routing_number'=>$request->number,
                'routing_number_type'=>$request->type,
            ]);
        if ($response->ok())
        {

            $data=$response->json();


            $bank->user_id=auth()->user()->id;
            $bank->bank_name=$response['bank_name'];
            $bank->routing_number=$response['routing_number'];
            $bank->routing_number_type=$response['routing_number_type'];


            $bank->supported_payment_types=json_encode($response['supported_payment_types']);
            $bank->bank_name=$response['bank_name'];
            $bank->deleted_at=null;
            $bank->bank_address=json_encode($response['bank_address']);
            if ($bank->save())
            {
                return redirect($this->URL)
                    ->with([
                        'toast' => [
                            'heading' => 'Message',
                            'message' => $this->TITLE . ' is created',
                            'type' => 'success',
                        ]
                    ]);
            }

        }
        else
        {
            $error=$response->json();
            return redirect($this->URL)
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' => $error['errors']['message'],
                        'type' => 'success',
                    ]
                ]);


        }



    }

    public function show($id)
    {
        $record = bank::findOrFail($id);
        if ($record->user_id != auth()->id()) {
            abort(404);
        }
        ;
        $record->supported_payment_types=json_decode($record->supported_payment_types);
        $record->bank_address=json_decode($record->bank_address);

        return view($this->VIEW . '.show', [
            'record' => $record,
        ]);
    }

    public function edit($id)
    {


        $record = bank::findOrFail($id);
        if ($record->user_id != auth()->id()) {
            abort(404);
        }
        return view($this->VIEW . '.edit', [
            'record' => $record
                ->orderBy('bank_name')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        abort(404);

        $record = bank::findOrFail($id);
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

        $record = bank::findOrFail($id);

        if ($record->user_id != auth()->id()) {
            abort(404);
        }
        $record->deleted_at=Carbon::now();
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
