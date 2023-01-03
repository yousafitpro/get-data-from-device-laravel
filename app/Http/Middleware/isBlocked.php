<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class isBlocked
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

        if (auth()->check()) {
            if (auth()->user()->status == "Deleted" || auth()->user()->status == "Blocked") {
                auth()->logout();
                if (Request::capture()->expectsJson()) {
                    return response()->json(['error' => 'Unauthorized or Blocked! Please contact the support team.'], 401);
                }
                return back()
                    ->with([
                        'toast' => [
                            'heading' => 'Message',
                            'message' =>' Unauthorized or Blocked! Please contact the support team.',
                            'type' => 'danger',
                        ]
                    ]);
                return redirect()->back();

            }
        }
        return $next($request);
    }
}
