<?php

namespace App\Http\Middleware;

use App\TaxableEntity;
use Closure;
use Illuminate\Support\Facades\Auth;

class AjaxTaxableEntityPermission
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
          $entity = TaxableEntity::find($request->route('id'));
          if ($entity->isSupplier()) {
            $permission = 'supplier-' . $action;
          } else if ($entity->isCustomer()) {
            $permission = 'customer-' . $action;
          } else if ($entity->isEmployee()) {
            $permission = 'user-' . $action;
          }
          if (!empty($permission) && $user->can($permission)) {
            return $next($request);
          }
    		}

        return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.No permission to access URL') ]]], 404);
    }
}
