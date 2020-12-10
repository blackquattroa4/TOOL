<?php

namespace App\Providers;

use App\MinifyBladeCompiler;
use Illuminate\View\Engines\CompilerEngine as BaseCompilerEngine;
use Illuminate\View\ViewServiceProvider as BaseViewServiceProvider;

class ViewServiceProvider extends BaseViewServiceProvider
{
    public function registerBladeEngine($resolver)
    {
        $this->app->singleton('blade.compiler', function () {
            return new MinifyBladeCompiler(
                $this->app['files'], $this->app['config']['view.compiled']
            );
        });

        $resolver->register('blade', function () {
            return new BaseCompilerEngine($this->app['blade.compiler']);
        });
    }
}
