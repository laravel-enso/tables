<?php

namespace LaravelEnso\Tables\Traits;

use Illuminate\Http\Request;
use LaravelEnso\Tables\Services\Data\FilterAggregator;
use LaravelEnso\Tables\Services\Data\Request as TableRequest;

trait ProvidesRequest
{
    public function request(Request $request)
    {
        $aggregator = new FilterAggregator(
            $request->get('internalFilters'),
            $request->get('filters'),
            $request->get('intervals'),
            $request->get('params')
        );

        return new TableRequest($request->get('columns'), $request->get('meta'), $aggregator);
    }
}
