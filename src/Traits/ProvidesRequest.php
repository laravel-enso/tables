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
            $this->payload($request->get('internalFilters')),
            $this->payload($request->get('filters')),
            $this->payload($request->get('intervals')),
            $this->payload($request->get('params'))
        );

        return new TableRequest(
            $this->payload($request->get('columns')),
            $this->payload($request->get('meta')),
            $aggregator()
        );
    }

    private function payload($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
}
