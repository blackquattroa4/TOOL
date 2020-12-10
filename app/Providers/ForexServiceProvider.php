<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class ForexServiceProvider extends ServiceProvider
{
  /**
   * instance of Forex service provider
   */
  private $serviceProvider;

  /**
   * Bootstrap the application services.
   *
   * @return void
   */
  public function boot()
  {
      $provider = '\\App\\' . str_replace('_', '', ucwords(config('forex.provider'), '_'));
      $this->serviceProvider = new $provider;
  }

  /**
   * Register the application services.
   *
   * @return void
   */
  public function register()
  {
    App::bind('Forex', function()
    {
      return $this->serviceProvider;
    });
  }
}
