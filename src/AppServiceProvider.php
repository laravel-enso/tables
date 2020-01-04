<?php

namespace LaravelEnso\Tables;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\Tables\App\Commands\TemplateCacheClear;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands(TemplateCacheClear::class);

        $this->load()
            ->publish();
    }

    private function load()
    {
        $this->mergeConfigFrom(__DIR__.'/config/tables.php', 'enso.tables');

        $this->mergeConfigFrom(__DIR__.'/config/api.php', 'enso.tables');

        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-enso/tables');

        return $this;
    }

    private function publish()
    {
        $this->publishes([
            __DIR__.'/config/tables.php' => config_path('enso/tables.php'),
        ], ['tables-config', 'enso-config']);

        $this->publishes([
            __DIR__.'/../stubs/Tables/Actions/CustomAction.stub' => app_path('Tables/Actions/CustomAction.php'),
            __DIR__.'/../stubs/Tables/Builders/ModelTable.stub' => app_path('Tables/Builders/ModelTable.php'),
            __DIR__.'/../stubs/Tables/Templates/template.stub' => app_path('Tables/Templates/template.json'),
        ], 'tables-resources');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/tables'),
        ], ['tables-mail', 'enso-mail']);
    }
}
