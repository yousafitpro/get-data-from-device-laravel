<?php

namespace App\Http\Middleware;

use App\Models\notificationSetting;
use App\Models\User;
use App\Models\UserSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TwoStepVerificationMiddleware
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


        if (auth()->check())
        {
            if (UserSetting::where('user_id',auth()->user()->id)->exists())
            {
                $setting=UserSetting::where('user_id',auth()->user()->id)->first();
                if ($setting->is_two_step_enabled=="false")
                {
                    Session::put("login_2FA",true);
                    Session::put("login_email_2FA",true);
                    return $next($request);
                }
            }
            if (!session('login_2FA',false) && notificationSetting::where([ 'name'=>'two_step_verification','user_id'=>auth()->user()->id])->exists() && notificationSetting::where([ 'name'=>'two_step_verification','user_id'=>auth()->user()->id])->value('sms')=='yes')
            {
                return redirect('security/2FA');
            }
            if (!session('login_email_2FA',false) && notificationSetting::where([ 'name'=>'two_step_verification','user_id'=>auth()->user()->id])->exists() && notificationSetting::where([ 'name'=>'two_step_verification','user_id'=>auth()->user()->id])->value('email')=='yes')
            {
                return redirect('security/email/2FA');
            }



        }



        return $next($request);
    }
}
