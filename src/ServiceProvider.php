<?php

namespace Oiuv\WorkEc;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(EC::class, function ($app) {
            return new EC(config('services.workec.corp_id'), config('services.workec.app_id'), config('services.workec.app_secret'));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [EC::class];
    }
}
