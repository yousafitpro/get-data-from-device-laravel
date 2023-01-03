<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\LocBankAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LocBankAccountController extends Controller
{

    public function remove($id)
    {
        if (LocBankAccount::where('deleted_at',null)->where('user_id',auth()->user()->id)->where('id',$id)->where('is_primary',true)->exists())
        {
            $ac=LocBankAccount::find($id);
            $ac->deleted_at=Carbon::now();
            $ac->is_primary=false;

            $ac->save();
            if(LocBankAccount::where('deleted_at',null)->where('user_id',auth()->user()->id)->where('user_id',auth()->user()->id)->get()->count()>0)
            {

                LocBankAccount::where('id','!=','dsd')->where('user_id',auth()->user()->id)->update(['is_primary'=>false]);
                $first=LocBankAccount::where('deleted_at',null)->where('user_id',auth()->user()->id)->where('id','!=',$id)->first();
                $first->is_primary=true;
                $first->save();

            }


        }
        $ac=LocBankAccount::find($id);
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
        LocBankAccount::where('id','!=','dsd')->where('user_id',auth()->user()->id)->update(['is_primary'=>false]);
        LocBankAccount::where('id',$id)->update(['is_primary'=>true]);
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
