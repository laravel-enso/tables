<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use LaravelEnso\Tables\app\Services\Excel as Service;

trait Excel
{
    use ProvidesData;

    public function __invoke(Request $request)
    {
        $user = $request->user();

        [$table, $config] = $this->data($request);

        (new Service(
            $user, $config, $this->tableClass
        ))->handle();
    }
}
