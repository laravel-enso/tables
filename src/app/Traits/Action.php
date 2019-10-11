<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\Data\Config;

trait Action
{
    use ProvidesData;

    public function __invoke(Request $request)
    {
        [$table, $config] = $this->data($request);

        App::make($this->actionClass, [
            'table' => $table,
            'config' => $config,
        ])->handle();
    }
}
