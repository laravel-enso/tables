<?php

namespace LaravelEnso\Tables\Services\Template\Validators\Columns;

use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Exceptions\Column as Exception;

class Columns
{
    private Obj $columns;

    public function __construct(Obj $template)
    {
        $this->columns = $template->get('columns');
    }

    public function validate()
    {
        $this->format()
            ->columns();
    }

    private function columns()
    {
        $this->columns
            ->each(fn ($column) => (new Column($column))->validate());
    }

    private function format()
    {
        if ($this->invalidFormat() || $this->invalidChild()) {
            throw Exception::invalidFormat();
        }

        return $this;
    }

    private function invalidFormat()
    {
        return ! $this->columns instanceof Obj
            || $this->columns->isEmpty();
    }

    private function invalidChild()
    {
        return $this->columns
            ->some(fn ($column) => ! $column instanceof Obj);
    }
}
