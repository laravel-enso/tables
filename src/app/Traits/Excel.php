<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use LaravelEnso\Tables\app\Services\Excel as Service;

trait Excel
{
    public function __invoke(Request $request)
    {
        (new Service(
            $request->user(),
            $request->all(),
            $this->tableClass
        ))->handle();
    }
}
