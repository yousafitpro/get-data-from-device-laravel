<?php

namespace App\Http\Middleware;

use App\Models\ipaddress;
use Closure;
use Illuminate\Http\Request;

class CheckIpMiddleware
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

        if (ipaddress::where('ip',app_get_ip($request))->where('deleted_at',null)->exists()) {

            $data['message']="IP Restricted";
            if (Request::capture()->expectsJson())
            {

                return response()->json(['error' => 'IP Restricted'], 401);

            }
            echo view('pages.NotValidIp',$data);
            dd("");
        }
        return $next($request);
    }

}
