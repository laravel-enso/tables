<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Filters;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Contracts\Table;

class DummyTable implements Table{
    public function query(): Builder
    {
        return App::make(Builder::class);
    }

    public function templatePath(): string
    {
        return '';
    }
}
