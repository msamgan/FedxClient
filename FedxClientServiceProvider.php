<?php

namespace fedxClientProviders;

use Illuminate\Support\ServiceProvider;

class FedxClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }
}
