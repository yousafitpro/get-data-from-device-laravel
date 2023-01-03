<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ipController;
use Closure;
use Illuminate\Http\Request;

class trackVisitor
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
        ipController::save_visitor_info(app_get_ip($request));
        return $next($request);
    }
}
