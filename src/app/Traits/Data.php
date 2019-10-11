<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\Config;
use LaravelEnso\Tables\app\Services\TemplateLoader;
use LaravelEnso\Tables\app\Services\Table\Builders\Data as DataBuilder;
use LaravelEnso\Tables\app\Services\Table\Builders\Meta as MetaBuilder;

trait Data
{
    use ProvidesRequest;

    public function __invoke(Request $request)
    {
        $request = $this->request($request);
        $table = App::make($this->tableClass, ['request' => $request]);
        $template = (new TemplateLoader($table))->handle();
        $config = new Config($request, $template);

        return (new DataBuilder($table, $config))->toArray()
            + (new MetaBuilder($table, $config))->toArray();
    }
}
