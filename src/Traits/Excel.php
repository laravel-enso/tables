<?php

namespace LaravelEnso\Tables\Traits;

use Illuminate\Http\Request;
use LaravelEnso\Tables\Exports\EnsoPrepare;
use LaravelEnso\Tables\Exports\Prepare;

trait Excel
{
    use ProvidesData;

    public function __invoke(Request $request)
    {
        $user = $request->user();
        ['config' => $config] = $this->data($request);
        $attrs = [$user, $config, $this->tableClass];

        if ($config->isEnso()) {
            (new EnsoPrepare(...$attrs))->handle();
        } else {
            (new Prepare(...$attrs))->handle();
        }
    }
}
