<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\TemplateLoader;

trait Init
{
    public function __invoke()
    {
        return (new TemplateLoader(
            App::make($this->tableClass)
        ))->get();
    }
}
