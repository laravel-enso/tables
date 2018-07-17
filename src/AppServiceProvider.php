<?php

namespace LaravelEnso\VueDatatable;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadDependencies();

        $this->publishesAll();
    }

    private function loadDependencies()
    {
        $this->mergeConfigFrom(__DIR__.'/config/datatable.php', 'enso.datatable');

        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-enso/vuedatatable');
    }

    private function publishesAll()
    {
        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], 'vuedatatable-config');

        $this->publishes([
            __DIR__.'/resources/assets/js' => resource_path('assets/js'),
        ], 'vuedatatable-assets');

        $this->publishes([
            __DIR__.'/resources/assets/js' => resource_path('assets/js'),
        ], 'enso-assets');

        $this->publishes([
            __DIR__.'/app/Tables' => app_path('Tables'),
        ], 'tables');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/vuedatatable'),
        ], 'vuedatatable-mail');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/vuedatatable'),
        ], 'enso-mail');
    }

    public function register()
    {
        //
    }
}
