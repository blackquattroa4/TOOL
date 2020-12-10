<?php

namespace App\Http\Middleware;

use Closure;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class TrafficLogMiddleware
{
  /**
  * Handle an incoming request.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  \Closure  $next
  * @return mixed
  */
  public function handle($request, Closure $next)
  {
    return $next($request);
  }

  /**
  * Handle an outgoing response.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  \Closure  $next
  * @return mixed
  */
  public function terminate($request, $response)
  {
    if (env("ENABLE_TRAFFIC_LOG", false)) {
      // formatter for the logger
      $formatter = new LineFormatter(
          // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
          "[%datetime%] %level_name%: %message% %context% %extra%\n",
          "Y-m-d H:i:s", // Datetime format
          true, // allowInlineLineBreaks option, default false
          true  // ignoreEmptyContextAndExtra option, default false
        );
      // handler for the logger
      $handler = new StreamHandler(storage_path('logs' . DIRECTORY_SEPARATOR . 'traffic_capture.log'), Logger::DEBUG);
      $handler->setFormatter($formatter);
      // instantiate new logger
      $log = new Logger('traffic');
      $log->pushHandler($handler);
      $user = $request->user();
      $log->debug( $request->path() . "\n" .
        "address   " . $request->ip() . "\n" .
        // route() gives substituted url, route()->uri() gives un-substituted url, route()->parameters gives all substitutions
        "route     (" . $request->method() . ") " . $request->route()->uri() . /*" " . json_encode($request->route()->parameters()) .*/ "\n" .
        // // if $request->route()->uri() is not available (i.e. in Lumen)
        // // use preg_replace instead of str_replace, so we can dictate how many times should a string be replaced
        // $urlPath = substr(preg_replace(
        //     array_map(function($item) { return "/\\/" . $item . "\\//"; }, array_values($urlParam)),
        //     array_map(function($item) { return "/{" . $item . "}/"; }, array_keys($urlParam)),
        //     "/" . $request->path() . "/",
        //     1), 1, -1);
        "login     " . $user->name . " (#" . $user->id . ")\n" .
        "response  (" . $response->status() . ") " . $response->content());
    }
  }

}
