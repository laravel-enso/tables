<?php

namespace LaravelEnso\Tables\App\Services\Template\Validators\Columns;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Attributes\Column as Attributes;
use LaravelEnso\Tables\App\Attributes\Style;
use LaravelEnso\Tables\App\Exceptions\Column as Exception;

class Column
{
    private Obj $column;

    public function __construct(Obj $column)
    {
        $this->column = $column;
    }

    public function validate(): void
    {
        $this->mandatoryAttributes()
            ->optionalAttributes()
            ->meta()
            ->enum()
            ->tooltip()
            ->money()
            ->class()
            ->align();
    }

    private function mandatoryAttributes()
    {
        $diff = (new Collection(Attributes::Mandatory))
            ->diff($this->column->keys());

        if ($diff->isNotEmpty()) {
            throw Exception::missingAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function optionalAttributes()
    {
        $attributes = (new Collection(Attributes::Mandatory))
            ->merge(Attributes::Optional);

        $diff = $this->column->keys()->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Exception::unknownAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function meta()
    {
        if ($this->column->has('meta')) {
            Meta::validate($this->column);
        }

        return $this;
    }

    private function enum()
    {
        if ($this->column->has('enum')
            && ! class_exists($this->column->get('enum'))) {
            throw Exception::enumNotFound($this->column->get('enum'));
        }

        return $this;
    }

    private function tooltip()
    {
        if (property_exists($this->column, 'tooltip')
            && ! is_string($this->column->tooltip)) {
            throw Exception::invalidTooltip($this->column->get('name'));
        }

        return $this;
    }

    private function money()
    {
        if (property_exists($this->column, 'money')
            && ! is_object($this->column->money)) {
            throw Exception::invalidMoney($this->column->get('name'));
        }

        return $this;
    }

    private function class()
    {
        if (property_exists($this->column, 'class')
            && ! is_string($this->column->class)) {
            throw Exception::invalidClass($this->column->get('name'));
        }

        return $this;
    }

    private function align()
    {
        if (property_exists($this->column, 'align')
            && ! (new Collection(Style::Align))->contains($this->column->align)) {
            throw Exception::invalidAlign($this->column->get('name'));
        }

        return $this;
    }
}
