<?php

namespace App\Http\Middleware;

use App;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class AjaxSessionMaintainer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->user()) {
    			// session expired. since it's authenticated (remembered), reconstruct session
    			if (session('session_language') == null) {
    		      session([
    		        'session_language' => Auth::user()->language,
    		        // any other variables to stuff into session?
    		      ]);
    			}
    			// set preferred language
    			App::setLocale(session('session_language'));
    		}
        return $next($request);
    }
}
