<?php

namespace App\Http\Middleware;

use App\Models\ipaddress;
use Closure;
use Illuminate\Http\Request;

class CheckFPMiddleware
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

        if (ipaddress::where('device_fingerprint',get_device_fingerprint())->where('deleted_at',null)->exists()) {

            $data['message']="Device Restricted";
            if (Request::capture()->expectsJson())
            {

                return response()->json(['error' => 'Device Restricted'], 401);

            }
            echo view('pages.NotValidDevice',$data);
            dd("");
        }
        return $next($request);
    }
    //asdasd
}
