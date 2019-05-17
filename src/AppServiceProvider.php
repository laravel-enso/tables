<?php

namespace LaravelEnso\Tables;

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
        $this->mergeConfigFrom(__DIR__.'/config/tables.php', 'enso.tables');

        $this->mergeConfigFrom(__DIR__.'/config/api.php', 'enso.tables');

        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-enso/tables');

        return $this;
    }

    private function publishDependencies()
    {
        $this->publishes([
            __DIR__.'/config/tables.php' => config_path('enso/tables.php'),
        ], 'tables-config');

        $this->publishes([
            __DIR__.'/config/tables.php' => config_path('enso/tables.php'),
        ], 'enso-config');

        $this->publishes([
            __DIR__.'/../resources' => app_path(),
        ], 'tables-classes');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/tables'),
        ], 'tables-mail');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/tables'),
        ], 'enso-mail');
    }
}
