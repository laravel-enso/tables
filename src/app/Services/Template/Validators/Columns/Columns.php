<?php

namespace LaravelEnso\Tables\App\Services\Template\Validators\Columns;

use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Exceptions\Column as Exception;

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
        if ($this->wrongFormat() || $this->wrongChildFormat()) {
            throw Exception::wrongFormat();
        }

        return $this;
    }

    private function wrongFormat()
    {
        return ! $this->columns instanceof Obj
            || $this->columns->isEmpty();
    }

    private function wrongChildFormat()
    {
        return $this->columns
            ->some(fn ($column) => ! $column instanceof Obj);
    }
}
