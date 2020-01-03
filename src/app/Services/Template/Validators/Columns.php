<?php

namespace LaravelEnso\Tables\App\Services\Template\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Attributes\Column as Attributes;
use LaravelEnso\Tables\App\Attributes\Style;
use LaravelEnso\Tables\App\Exceptions\Column as Exception;

class Columns
{
    private $columns;

    public function __construct(Obj $template)
    {
        $this->columns = $template->get('columns');
    }

    public function validate()
    {
        $this->checkFormat();

        $this->columns->each(fn ($column) => $this->checkMandatoryAttributes($column)
            ->checkOptionalAttributes($column)
            ->checkMeta($column)
            ->checkEnum($column)
            ->checkTooltip($column)
            ->checkMoney($column)
            ->checkClass($column)
            ->checkAlign($column)
        );
    }

    private function checkFormat()
    {
        if (! $this->columns instanceof Obj
            || $this->columns->isEmpty()
            || $this->columns->first(fn ($column) => ! $column instanceof Obj) !== null
        ) {
            throw Exception::wrongFormat();
        }
    }

    private function checkMandatoryAttributes($column)
    {
        $diff = (new Collection(Attributes::Mandatory))
            ->diff($column->keys());

        if ($diff->isNotEmpty()) {
            throw Exception::missingAttributes(
                $diff->implode('", "')
            );
        }

        return $this;
    }

    private function checkOptionalAttributes($column)
    {
        $attributes = (new Collection(Attributes::Mandatory))
            ->merge(Attributes::Optional);

        $diff = $column->keys()->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Exception::unknownAttributes(
                $diff->implode('", "')
            );
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
                throw Exception::enumNotFound(
                    $column->get('enum')
                );
            }
        }

        return $this;
    }

    private function checkTooltip($column)
    {
        if (property_exists($column, 'tooltip') && ! is_string($column->tooltip)) {
            throw Exception::invalidTooltip(
                $column->get('name')
            );
        }

        return $this;
    }

    private function checkMoney($column)
    {
        if (property_exists($column, 'money') && ! is_object($column->money)) {
            throw Exception::invalidMoney(
                $column->get('name')
            );
        }

        return $this;
    }

    private function checkClass($column)
    {
        if (property_exists($column, 'class') && ! is_string($column->class)) {
            throw Exception::invalidClass(
                $column->get('name')
            );
        }

        return $this;
    }

    private function checkAlign($column)
    {
        if (property_exists($column, 'align')
            && ! (new Collection(Style::Align))->contains($column->align)) {
            throw Exception::invalidAlign(
                $column->get('name')
            );
        }

        return $this;
    }
}
