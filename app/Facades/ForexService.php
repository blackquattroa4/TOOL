<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ForexService extends Facade
{
  protected static function getFacadeAccessor()
  {
    return 'Forex';
  }
}
