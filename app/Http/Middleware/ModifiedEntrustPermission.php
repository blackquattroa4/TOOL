<?php

namespace App\Http\Middleware;

use Zizaco\Entrust\Middleware\EntrustPermission;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Zizaco\Entrust
 */

use Closure;

class ModifiedEntrustPermission extends EntrustPermission
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  Closure $next
	 * @param  $permissions
	 * @return mixed
	 */
	public function handle($request, Closure $next, $permissions)
	{
		if ($this->auth->guest() || !$request->user()->can(explode('|', $permissions))) {
			// can not redirect back to a specific url i.e. redirect('...')
			// because middleware will be triggered again, and thus begin infinite loop
			return redirect()->back()->with("alert-warning", trans("messages.No permission to access URL"));
		}

		return $next($request);
	}
}
