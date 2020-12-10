<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class EcommerceService extends Facade
{
  protected static function getFacadeAccessor()
  {
    return 'Ecommerce';
  }
}
