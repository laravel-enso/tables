<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\Template;

trait Init
{
    public function __invoke()
    {
        return (new Template(
            App::make($this->tableClass)
        ))->get();
    }
}
