<?php

namespace App\Http\Controllers;


use App\Models\packageTransaction;
use App\Models\User;
use App\Models\UserSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{

    public function accept_agreement_now()
    {
        $user=User::find(auth()->user()->id);
        $user->is_agree='true';
        $user->save();
        if(Request::capture()->expectsJson())
        {
            return response()->json(['message'=>"Agreement Accepted"]);
        }
        return redirect('dashboard');
    }
    public function accept_agreement()
    {
               return view('subscription.accept_agreement');
    }
    public function renew_package()
    {
        $data['is_has_pending']=false;
        $u=User::find(auth()->user()->id);
        $us=UserSetting::where("user_id",auth()->user()->id)->first();

        if ($u->valid_till!='renew' && $us->is_membership_expired=='false')
        {
            return redirect('/dashboard');
        }
        $end = \Carbon\Carbon::parse(time_now())->subMinutes(5);
        $start =\Carbon\Carbon::parse(time_now())->subDays(30);
        packageTransaction::where(['user_id'=>auth()->user()->id,'status'=>'Pending'])->whereBetween('created_at',[$start,$end])->update(['status'=>'Canceled']);

        if (packageTransaction::where(['user_id'=>auth()->user()->id])->where('status','Pending')->exists())
        {
            $data['is_has_pending']=true;

        }

        return view('subscription.renew_package',$data);
    }
}
