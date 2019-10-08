<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Builders;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Contracts\Table;

class TestTable implements Table {

    private $select;

    public function select($select)
    {
        $this->select = $select;

        return $this;
    }

    public function query(): Builder
    {
        return TestModel::selectRaw($this->select);
    }

    public function templatePath(): string
    {
        return __DIR__.'/../../stubs/template.json';
    }
}
