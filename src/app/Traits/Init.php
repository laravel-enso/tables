<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Support\Facades\App;

trait Init
{
    public function __invoke()
    {
        return App::make($this->tableClass)->init();
    }
}
