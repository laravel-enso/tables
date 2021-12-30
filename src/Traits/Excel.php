<?php

namespace LaravelEnso\Tables\Traits;

use Illuminate\Http\Request;
use LaravelEnso\Tables\Exports\Prepare;
use LaravelEnso\Tables\Notifications\ExportStarted;

trait Excel
{
    use ProvidesData;

    public function __invoke(Request $request)
    {
        $user = $request->user();
        ['config' => $config] = $this->data($request);
        $attrs = [$user, $config, $this->tableClass];

        $user->notifyNow(new ExportStarted($config->label()));

        (new Prepare(...$attrs))->handle();
    }
}
