<?php

namespace LaravelEnso\Tables;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use LaravelEnso\Tables\Commands\TemplateCacheClear;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->load()
            ->publish()
            ->commands(TemplateCacheClear::class);
    }

    private function load()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tables.php', 'enso.tables');

        $this->mergeConfigFrom(__DIR__.'/../config/api.php', 'enso.tables');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-enso/tables');

        return $this;
    }

    private function publish()
    {
        $this->publishes([
            __DIR__.'/../config/tables.php' => config_path('enso/tables.php'),
        ], ['tables-config', 'enso-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-enso/tables'),
        ], ['tables-mail', 'enso-mail']);

        $this->stubs()->each(fn ($ext, $stub) => $this->publishes([
            __DIR__."/../stubs/{$stub}.stub" => app_path("{$stub}.{$ext}"),
        ]));

        return $this;
    }

    private function stubs()
    {
        return new Collection([
            'Tables/Actions/CustomAction' => 'php',
            'Tables/Builders/ModelTable' => 'php',
            'Tables/Templates/template' => 'json',
        ]);
    }
}
