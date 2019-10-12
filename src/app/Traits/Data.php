<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use LaravelEnso\Tables\app\Services\Data\Builders\Data as DataBuilder;
use LaravelEnso\Tables\app\Services\Data\Builders\Meta as MetaBuilder;

trait Data
{
    use ProvidesData;

    public function __invoke(Request $request)
    {
        ['table' => $table, 'config' => $config] = $this->data($request);

        return (new DataBuilder($table, $config))->toArray()
            + (new MetaBuilder($table, $config))->toArray();
    }
}
