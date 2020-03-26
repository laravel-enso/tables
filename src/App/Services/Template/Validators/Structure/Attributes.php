<?php

namespace LaravelEnso\Tables\App\Services\Template\Validators\Structure;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Exceptions\Template as Exception;

class Attributes
{
    private Obj $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function validate(): void
    {
        $this->lengthMenu()
            ->appends()
            ->searchMode()
            ->debounce()
            ->method()
            ->selectable()
            ->comparisonOperator();
    }

    private function lengthMenu()
    {
        if ($this->template->has('lengthMenu')
            && ! $this->template->get('lengthMenu') instanceof Obj) {
            throw Exception::invalidLengthMenu();
        }

        return $this;
    }

    private function appends()
    {
        if ($this->template->has('appends')
            && ! $this->template->get('appends') instanceof Obj) {
            throw Exception::invalidAppends();
        }

        return $this;
    }

    private function searchMode()
    {
        if ($this->template->has('searchModes')
            && ! $this->template->get('searchModes') instanceof Obj) {
            throw Exception::invalidSearchModes();
        }

        return $this;
    }

    private function debounce()
    {
        if ($this->template->has('debounce')
            && ! is_int($this->template->get('debounce'))) {
            throw Exception::invalidDebounce();
        }

        return $this;
    }

    private function method()
    {
        if ($this->template->has('method')
            && ! (new Collection(['GET', 'POST']))
            ->contains($this->template->get('method'))) {
            throw Exception::invalidMethod();
        }

        return $this;
    }

    private function selectable()
    {
        if ($this->template->has('selectable')
            && ! is_bool($this->template->get('selectable'))) {
            throw Exception::invalidSelectable();
        }

        return $this;
    }

    private function comparisonOperator()
    {
        if ($this->template->has('comparisonOperator')
            && ! (new Collection(['LIKE', 'ILIKE']))
                ->contains($this->template->get('comparisonOperator'))) {
            throw Exception::invalidComparisonOperator();
        }
    }
}
