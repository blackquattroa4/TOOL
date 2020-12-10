<?php

namespace App\Http\Middleware;

use App\Exceptions\AjaxException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AjaxAuthenticate extends Middleware
{
  /**
   * Handle an unauthenticated user.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  array  $guards
   * @return void
   *
   * @throws \Illuminate\Auth\AuthenticationException
   */
  protected function unauthenticated($request, array $guards)
  {
      throw new AjaxException(401, 'Unauthenticated.');
  }
}
