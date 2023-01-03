<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\LocBankAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function remove($id)
    {



        if (BankAccount::where('deleted_at',null)->where('id',$id)->where('is_primary',true)->exists())
        {
            $ac=BankAccount::find($id);
            $ac->deleted_at=Carbon::now();
            $ac->is_primary=false;
            $ac->save();
            if(BankAccount::where('deleted_at',null)->get()->count()>0)
            {
                BankAccount::where('id','!=','dsd')->update(['is_primary'=>false]);
                $first=BankAccount::where('deleted_at',null)->where('id','!=',$id)->first();
                $first->is_primary=true;
                $first->save();
            }

        }
        $ac=BankAccount::find($id);
        $ac->deleted_at=Carbon::now();
        $ac->save();

        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>"Operation Successful"]);
        }
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Account Success fully removed',
                    'type' => 'error',
                ]
            ]);
    }
    public function set_primary($id)
    {
        BankAccount::where('id','!=','dsd')->update(['is_primary'=>false]);
        BankAccount::where('id',$id)->update(['is_primary'=>true]);
        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>"Operation Successful"]);
        }
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Operation successful',
                    'type' => 'success',
                ]
            ]);
    }
}
