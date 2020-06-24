<?php

namespace LaravelEnso\Tables\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

trait Action
{
    use ProvidesData;

    public function __invoke(Request $request)
    {
        App::make($this->actionClass, $this->data($request))->handle();
    }
}
