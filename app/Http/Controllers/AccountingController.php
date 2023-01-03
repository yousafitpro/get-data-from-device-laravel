<?php

namespace App\Http\Controllers;

use App\Models\AccountingConnection;
use Illuminate\Http\Request;
use PHPUnit\Exception;

class AccountingController extends Controller
{
    public function index()
    {
        $data['list']=AccountingConnection::where('user_id',auth()->user()->id)->where('deleted_at',null)->get();
        return view('accounting.index',$data);
    }

    public function create_business(Request $request)
    {
        $data=$request->except(['_token']);
        $data['user_id']=auth()->user()->id;
        AccountingConnection::create($data);
        return redirect()
            ->back()->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Business has been Added.',
                    'type' => 'success',
                ]
            ]);
    }
    public function delete_business(Request $request,$id)
    {
        AccountingConnection::where('id',$id)->update([
            'deleted_at'=>time_now()
        ]);

        return redirect()
            ->back()->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Business has been Deleted.',
                    'type' => 'success',
                ]
            ]);
    }
    public function accounting_services_list()
    {
        //asa
        return view('accounting.accounting_services');
    }
    public static function sendDataToServices($user,$vendorName,$issue_date,$amount)
    {
        // -------------------------freshbook
        $freshbooks=AccountingConnection::where([
            'user_id'=>$user->id,
            'deleted_at'=>null,
            'service_name'=>'Freshbooks',
        ])->where(function ($query){
            $query->where('status','connected');
            $query->orWhere('status','expired');
        })->get();

        foreach ($freshbooks as $b)
        {
            try {
                freshbookCOntroller::checkTokenIsValid($b->id);
                freshbookCOntroller::sendTransaction($b->id,$vendorName,$issue_date,$amount);
            }catch (Exception $e)
            {

            }
        }
// -------------------------quickbook
        $quickbooks=AccountingConnection::where([
            'user_id'=>$user->id,
            'deleted_at'=>null,
            'service_name'=>'Quickbooks',
        ])->where(function ($query){
            $query->where('status','connected');
            $query->orWhere('status','expired');
        })->get();

        foreach ($quickbooks as $b)
        {
            try {
                quickbookController::checkTokenIsValid($b->id);
                quickbookController::sendTransaction($b->id,$vendorName,$issue_date,$amount);
            }catch (Exception $e)
            {

            }
        }

    }

}
