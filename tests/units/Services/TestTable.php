<?php

namespace LaravelEnso\Tables\Tests\units\Services;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Contracts\Table;

class TestTable implements Table
{
    public function query(): Builder
    {
        return TestModel::selectRaw('id, name, is_active, created_at, price, color');
    }

    public function templatePath(): string
    {
        return __DIR__.'/stubs/template.json';
    }
}
