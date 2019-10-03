<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Builders;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Contracts\Table;

class TestTable implements Table {

    private $select;

    public function __construct($select)
    {
        $this->select = $select;
    }

    public function query(): Builder
    {
        return TestModel::selectRaw($this->select);
    }

    public function templatePath(): string
    {
        return '';
    }
}
