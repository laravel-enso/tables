<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

trait Data
{
    public function __invoke(Request $request)
    {
        return App::make(
            $this->tableClass,
            ['request' => $request->all(),
        ])->data();
    }
}
