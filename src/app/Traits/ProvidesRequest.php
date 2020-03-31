<?php

namespace LaravelEnso\Tables\App\Traits;

use Illuminate\Http\Request;
use LaravelEnso\Tables\App\Services\Data\Request as TableRequest;

trait ProvidesRequest
{
    public function request(Request $request)
    {
        return new TableRequest(
            $request->get('columns'),
            $request->get('meta'),
            $request->get('internalFilters'),
            $request->get('filters'),
            $request->get('intervals'),
            $request->get('params')
        );
    }
}
