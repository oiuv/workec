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
            return new EC(env('EC_CORP_ID') || config('services.workec.corp_id'), env('EC_APP_ID') || config('services.workec.app_id'), env('EC_APP_SECRET') || config('services.workec.app_secret'));
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
