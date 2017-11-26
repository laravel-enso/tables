<?php

namespace LaravelEnso\VueDatatable;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config' => config_path('enso'),
        ], 'vuedatatable-config');

        $this->publishes([
            __DIR__ . '/resources/assets/js' => resource_path('assets/js'),
        ], 'vuedatatable-assets');
    }

    public function register()
    {
        //
    }
}
