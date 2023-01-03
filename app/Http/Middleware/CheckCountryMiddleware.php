<?php

namespace App\Http\Middleware;

use App\Jobs\RemoveRestrictionAfter24HoursJob;
use App\Models\appcountry;
use App\Models\ipaddress;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckCountryMiddleware
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

        $ip=app_get_ip($request);
        $req=Http::get('http://ip-api.com/json/'.$ip);
        if ($req->status()=='200' && $req->json()['status']=='success') {

            if (!appcountry::where('countryCode', $req->json()['countryCode'])->where('deleted_at', null)->exists()) {

                $data['message'] = "Not Allowed In Your Country";
                $ip= ipaddress::create([
                    'user_id'=>auth()->check()?auth()->id():null,
                    'ip'=>$ip
                ]);
                $tempData['id']=$ip->id;
                RemoveRestrictionAfter24HoursJob::dispatch($tempData)->delay(Carbon::now(config('app.timezone'))->addHours(24));
                if (Request::capture()->expectsJson())
                {

                        return response()->json(['error' => 'Not Allowed In Your Country'], 401);

                }
                echo view('pages.NotAllowedInCountry', $data);
                dd("");
            }
        }
        return $next($request);
    }

}
