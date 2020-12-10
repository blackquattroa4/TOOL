<?php

namespace App\Contracts;

interface ForexServiceContract
{

  // function that provides currency exchange-rate.
  public static function getExchangeRate($from, $to, $simplified = false);

}
