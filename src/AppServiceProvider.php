<?php

namespace LaravelEnso\VueDatatable;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishesResources();
        $this->loadDependencies();
    }

    private function publishesResources()
    {
        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], 'vuedatatable-config');

        $this->publishes([
            __DIR__.'/resources/assets/js' => resource_path('assets/js'),
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/vuedatatable'),
        ], 'vuedatatable-assets');

        $this->publishes([
            __DIR__.'/resources/assets/js' => resource_path('assets/js'),
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/vuedatatable'),
        ], 'enso-assets');

        $this->publishes([
            __DIR__.'/app/Tabels' => app_path('Tables'),
        ], 'tables');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/vuedatatable'),
        ], 'vuedatatable-email-templates');
    }

    private function loadDependencies()
    {
        $this->mergeConfigFrom(__DIR__.'/config/datatable.php', 'enso.datatable');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-enso/vuedatatable');
    }

    public function register()
    {
        //
    }
}
