<?php

namespace LaravelEnso\Tables\Services\Template\Validators\Structure;

use Illuminate\Support\Str;
use LaravelEnso\Filters\Enums\ComparisonOperators;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Exceptions\Template as Exception;

class Attributes
{
    public function __construct(private Obj $template)
    {
    }

    public function validate(): void
    {
        $this->lengthMenu()
            ->appends()
            ->searchMode()
            ->defaultSortDirection()
            ->debounce()
            ->method()
            ->selectable()
            ->comparisonOperator();
    }

    private function lengthMenu()
    {
        if (
            $this->template->has('lengthMenu')
            && ! $this->template->get('lengthMenu') instanceof Obj
        ) {
            throw Exception::invalidLengthMenu();
        }

        return $this;
    }

    private function appends()
    {
        if (
            $this->template->has('appends')
            && ! $this->template->get('appends') instanceof Obj
        ) {
            throw Exception::invalidAppends();
        }

        return $this;
    }

    private function searchMode()
    {
        if (
            $this->template->has('searchModes')
            && ! $this->template->get('searchModes') instanceof Obj
        ) {
            throw Exception::invalidSearchModes();
        }

        return $this;
    }

    private function defaultSortDirection()
    {
        $allowed = ['asc', 'desc'];

        if (
            $this->template->has('defaultSortDirection')
            && ! in_array(Str::lower($this->template->get('defaultSortDirection')), $allowed)
        ) {
            throw Exception::invalidSortDirection();
        }

        return $this;
    }

    private function debounce()
    {
        if (
            $this->template->has('debounce')
            && ! is_int($this->template->get('debounce'))
        ) {
            throw Exception::invalidDebounce();
        }

        return $this;
    }

    private function method()
    {
        $invalid = $this->template->has('method')
            && ! in_array($this->template->get('method'), ['GET', 'POST']);

        if ($invalid) {
            throw Exception::invalidMethod();
        }

        return $this;
    }

    private function selectable()
    {
        if (
            $this->template->has('selectable')
            && ! is_bool($this->template->get('selectable'))
        ) {
            throw Exception::invalidSelectable();
        }

        return $this;
    }

    private function comparisonOperator()
    {
        $invalid = $this->template->has('comparisonOperator')
            && $this->template->get('comparisonOperator') !== ComparisonOperators::Like
            && $this->template->get('comparisonOperator') !== ComparisonOperators::ILike;

        if ($invalid) {
            throw Exception::invalidComparisonOperator();
        }
    }
}
