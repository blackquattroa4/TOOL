<?php

namespace App\Http\Middleware;

use App;
use App\Helpers\S3DownloadHelper;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class SessionMaintainer
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
    			if ((session('session_language') == null) ||
    				(session('session_url_links') == null)) {
    		      session([
    		        'session_language' => Auth::user()->language,
    		        'session_url_links' => [ "/" ],
    		        // any other variables to stuff into session?
    		      ]);
    			}
    			// set preferred language
    			App::setLocale(session('session_language'));
    			// set last-N-pages-viewed
    			$visitedPages = session('session_url_links');
    			if (request()->path() != $visitedPages[0]) {
    				array_unshift($visitedPages, request()->path());
    			}
    			session(['session_url_links' => array_slice($visitedPages, 0, env('LAST_N_VISITED_PAGE'))]);
    		}
        return $next($request);
    }
}
