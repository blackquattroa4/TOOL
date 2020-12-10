<?php

namespace App\Helpers;

class RouteHelper
{
  // with a given class & method, do a reverse-lookup for route
  public static function appReverseLookupRoute($classAndMethod) {
    $route = "";
    $cnt = 0 - strlen($classAndMethod);
    foreach (app()->routes as $oneRoute) {
      if (strcasecmp($classAndMethod, substr($oneRoute->getActionName(), $cnt)) == 0) {
        $route = $oneRoute->uri();
        break;
      }
    }
    return $route;
  }

}

?>
