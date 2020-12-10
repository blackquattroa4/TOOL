<?php

namespace App\Http\Middleware;

use App\PurchaseHeader;
use Closure;
use Illuminate\Support\Facades\Auth;

class AjaxPurchaseEntryPermission
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
          $purchaseHeader = PurchaseHeader::find($request->route('id'));
          if ($purchaseHeader->isOrder()) {
            $permission = 'po-' . $action;
          } else if ($purchaseHeader->isReturn()) {
            $permission = 'pr-' . $action;
          } else if ($purchaseHeader->isQuote()) {
            $permission = 'pq-' . $action;
          }
          if (!empty($permission) && $user->can($permission)) {
            return $next($request);
          }
    		}

        return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.No permission to access URL') ]]], 404);
    }
}
