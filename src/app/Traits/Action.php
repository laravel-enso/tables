<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

trait Action
{
    public function __invoke(Request $request)
    {
        $request = new TableRequest($request->all());
        $table = App::make($this->tableClass, ['request' => $request]);

        App::make($this->actionClass, [
            'table' => $table,
            'request' => $request,
        ])->handle();
    }
}
