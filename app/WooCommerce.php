<?php

namespace App;

use App\Contracts\EcommerceServiceContract;

class WooCommerce implements EcommerceServiceContract
{

  // :TODO: implement this function
  public static function orderSync()
  {
    return "woo-commerce ecommerce order-sync";
  }

}
