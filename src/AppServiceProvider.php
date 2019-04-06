<?php

namespace LaravelEnso\VueDatatable;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadDependencies()
            ->publishDependencies();
    }

    private function loadDependencies()
    {
        $this->mergeConfigFrom(__DIR__.'/config/datatable.php', 'enso.datatable');

        $this->mergeConfigFrom(__DIR__.'/config/api.php', 'enso.datatable');

        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-enso/vuedatatable');

        return $this;
    }

    private function publishDependencies()
    {
        $this->publishes([
            __DIR__.'/config/datatable.php' => config_path('enso/datatable.php'),
        ], 'vuedatatable-config');

        $this->publishes([
            __DIR__.'/config/datatable.php' => config_path('enso/datatable.php'),
        ], 'enso-config');

        $this->publishes([
            __DIR__.'/../resources' => app_path(),
        ], 'vuedatatable-classes');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/vuedatatable'),
        ], 'vuedatatable-mail');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/vuedatatable'),
        ], 'enso-mail');
    }
}
