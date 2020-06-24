<?php

namespace LaravelEnso\Tables\Traits;

use Illuminate\Http\Request;
use LaravelEnso\Tables\Services\Excel as Service;

trait Excel
{
    use ProvidesData;

    public function __invoke(Request $request)
    {
        $user = $request->user();

        ['config' => $config] = $this->data($request);

        (new Service(
            $user, $config, $this->tableClass
        ))->handle();
    }
}
