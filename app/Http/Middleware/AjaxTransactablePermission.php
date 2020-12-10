<?php

namespace App\Http\Middleware;

use App\TransactableHeader;
use Closure;
use Illuminate\Support\Facades\Auth;

class AjaxTransactablePermission
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
          $permission = '';
          $transactableHeader = TransactableHeader::find($request->route('id'));
          if ($transactableHeader->isReceivable()) {  // receivable
            if ($transactableHeader->isInvoice()) {
              $permission = 'ar-' . $action;
            } else {
              $permission = 'rar-' . $action;
            }
          } else {  // payable
            if ($transactableHeader->isInvoice()) {
              $permission = 'ap-' . $action;
            } else {
              $permission = 'rap-' . $action;
            }
          }
          if (!empty($permission) && $user->can($permission)) {
            return $next($request);
          }
    		}

        return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.No permission to access URL') ]]], 404);
    }
}
