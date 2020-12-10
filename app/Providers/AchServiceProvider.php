<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AchServiceProvider extends ServiceProvider
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
      $provider = '\\App\\' . str_replace('_', '', ucwords(config('ach.provider'), '_'));
      $this->serviceProvider = new $provider;
  }

  /**
   * Register the application services.
   *
   * @return void
   */
  public function register()
  {
    App::bind('ACH', function()
    {
      return $this->serviceProvider;
    });
  }
}
