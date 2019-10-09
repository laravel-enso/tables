<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\TemplateLoader;
use LaravelEnso\Tables\app\Services\Table\Config;
use LaravelEnso\Tables\app\Services\Table\Builders\Data as DataBuilder;
use LaravelEnso\Tables\app\Services\Table\Builders\Meta as MetaBuilder;

trait Data
{
    public function __invoke(Request $request)
    {
        $config = new Config($request->all());
        $table = App::make($this->tableClass, ['config' => $config]);
        $config->setTemplate(TemplateLoader::load($table));

        return ['data' => (new DataBuilder($config))->data()]
            + (new MetaBuilder($config))->data();
    }
}
