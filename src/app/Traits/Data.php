<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;

trait Data
{
    public function __invoke(Request $request)
    {
        return (new $this->tableClass($request->all()))->data();
    }
}
