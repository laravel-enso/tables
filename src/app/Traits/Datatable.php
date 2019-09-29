<?php

namespace LaravelEnso\Tables\app\Traits;

use App;
use Illuminate\Http\Request;
use LaravelEnso\Tables\app\Services\Template;
use LaravelEnso\Tables\app\Services\Table\Request as TableRequest;
use LaravelEnso\Tables\app\Services\Table\Builders\Data as DataBuilder;
use LaravelEnso\Tables\app\Services\Table\Builders\Meta as MetaBuilder;

trait Datatable
{
    public function init()
    {
        return (new Template(new $this->tableClass()))
            ->get();
    }

    public function data(Request $request)
    {
        $request = new TableRequest($request->all());

        $table = App::make($this->tableClass, ['request' => $request]);

        return ['data' => (new DataBuilder($table, $request))->data()] +
            (new MetaBuilder($table, $request))->data();
    }
}
