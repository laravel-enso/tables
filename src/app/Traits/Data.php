<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

trait Data
{
    public function __invoke(Request $request)
    {
        return App::makeWith(
            $this->tableClass,
            ['request' => $request->all()
        ])->data();
    }
}
