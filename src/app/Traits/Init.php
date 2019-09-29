<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\TemplateCache;

trait Init
{
    public function __invoke()
    {
        return (new TemplateCache(
            App::make($this->tableClass)
        ))->get();
    }
}
