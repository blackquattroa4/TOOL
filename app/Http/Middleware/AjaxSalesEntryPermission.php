<?php

namespace App\Http\Middleware;

use App\SalesHeader;
use Closure;
use Illuminate\Support\Facades\Auth;

class AjaxSalesEntryPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $action, $guard = null)
    {
        if ($user = Auth::guard($guard)->user()) {
          if ($request->route('id') == 0) {
            return $next($request);
          }
          $permission = '';
          $salesHeader = SalesHeader::find($request->route('id'));
          if ($salesHeader->isOrder()) {
            $permission = 'so-' . $action;
          } else if ($salesHeader->isReturn()) {
            $permission = 'sr-' . $action;
          } else if ($salesHeader->isQuote()) {
            $permission = 'sq-' . $action;
          }
          if (!empty($permission) && $user->can($permission)) {
            return $next($request);
          }
    		}

        return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.No permission to access URL') ]]], 404);
    }
}
