<?php

namespace LaravelEnso\Tables\app\Services\Template\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Attributes\Style;
use LaravelEnso\Tables\app\Exceptions\TemplateException;
use LaravelEnso\Tables\app\Attributes\Column as Attributes;

class Columns
{
    private $columns;

    public function __construct($template)
    {
        $this->columns = $template->get('columns');
    }

    public function validate()
    {
        $this->checkFormat();

        $this->columns->each(function ($column) {
            $this->checkMandatoryAttributes($column)
                ->checkOptionalAttributes($column)
                ->checkMeta($column)
                ->checkEnum($column)
                ->checkTooltip($column)
                ->checkMoney($column)
                ->checkClass($column)
                ->checkAlign($column);
        });
    }

    private function checkFormat()
    {
        if (! $this->columns instanceof Obj || $this->columns->isEmpty()
            || $this->columns->first(function ($column) {
                return ! $column instanceof Obj;
            }) !== null
        ) {
            throw new TemplateException(__(
                'The columns attribute must be an array of objects with at least one element'
            ));
        }
    }

    private function checkMandatoryAttributes($column)
    {
        $diff = collect(Attributes::Mandatory)
            ->diff($column->keys());

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Mandatory column attribute(s) missing: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkOptionalAttributes($column)
    {
        $attributes = collect(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = $column->keys()->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Unknown Column Attribute(s) Found: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkMeta($column)
    {
        if ($column->has('meta')) {
            Meta::validate($column);
        }

        return $this;
    }

    private function checkEnum($column)
    {
        if ($column->has('enum')) {
            if (! class_exists($column->get('enum'))) {
                throw new TemplateException(__(
                    'Provided enum does not exist: ":enum"',
                    ['enum' => $column->get('enum')]
                ));
            }
        }

        return $this;
    }

    private function checkTooltip($column)
    {
        if (property_exists($column, 'tooltip') && ! is_string($column->tooltip)) {
            throw new TemplateException(__(
                'The tooltip attribute provided for ":column" must be a string',
                ['column' => $column->get('name')]
            ));
        }

        return $this;
    }

    private function checkMoney($column)
    {
        if (property_exists($column, 'money') && ! is_object($column->money)) {
            throw new TemplateException(__(
                'Provided money attribute for ":column" must be a non empty object',
                ['column' => $column->get('name')]
            ));
        }

        return $this;
    }

    private function checkClass($column)
    {
        if (property_exists($column, 'class') && ! is_string($column->class)) {
            throw new TemplateException(__(
                'The class attribute provided for ":column" must be a string',
                ['column' => $column->get('name')]
            ));
        }

        return $this;
    }

    private function checkAlign($column)
    {
        if (property_exists($column, 'align')
            && ! collect(Style::Align)->contains($column->align)) {
            throw new TemplateException(__(
                'The align attribute provided for ":column" is incorrect',
                ['column' => $column->get('name')]
            ));
        }

        return $this;
    }
}
