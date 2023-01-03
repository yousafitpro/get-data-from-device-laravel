<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ENVController;
use App\Models\UserSetting;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class SubscriptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
//        if (auth()->user()->is_agree=='false')
//        {
//            return redirect('security/accept-agreement');
//        }
//
//        if (auth()->user()->id!=who_is_admin() && (auth()->user()->valid_till<=Carbon::now()->toDateString() || auth()->user()->valid_till=='renew' || auth()->user()->valid_till==null))
//        {
//            ENVController::beforeLogin(auth()->user());
//            $p=UserSetting::where('user_id',auth()->user()->id)->first();
//            $p->is_membership_expired='true';
//            $p->save();
//            if (Request::capture()->expectsJson())
//            {
//                return response()->json(['message'=>"Please renew Package"]);
//            }
//            return redirect('security/renew-package');
//        }
        //sasa
        return $next($request);
    }
}
