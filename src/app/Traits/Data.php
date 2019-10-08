<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\TemplateLoader;
use LaravelEnso\Tables\app\Services\Table\Request as TableRequest;
use LaravelEnso\Tables\app\Services\Table\Builders\Data as DataBuilder;
use LaravelEnso\Tables\app\Services\Table\Builders\Meta as MetaBuilder;

trait Data
{
    public function __invoke(Request $request)
    {
        $request = new TableRequest($request->all());
        $table = App::make($this->tableClass, compact($request));
        $template = (new TemplateLoader($table))->handle();

        return ['data' => (new DataBuilder($table, $request, $template))->data()]
            + (new MetaBuilder($table, $request, $template))->data();
    }
}
