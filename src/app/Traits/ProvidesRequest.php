<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use LaravelEnso\Tables\app\Services\Data\Request as TableRequest;

trait ProvidesRequest
{
    public function request(Request $request)
    {
        return new TableRequest(
            $request->get('columns'),
            $request->get('meta'),
            $request->get('filters'),
            $request->get('intervals'),
            $request->get('params')
        );
    }
}