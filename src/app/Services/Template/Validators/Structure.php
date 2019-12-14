<?php

namespace LaravelEnso\Tables\app\Services\Template\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Attributes\Structure as Attributes;
use LaravelEnso\Tables\app\Exceptions\Template as Exception;

class Structure
{
    private $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $this->checkMandatoryAttributes()
            ->checkOptionalAttributes()
            ->checkFormat();
    }

    private function checkMandatoryAttributes()
    {
        $diff = collect(Attributes::Mandatory)
            ->diff($this->template->keys());

        if ($diff->isNotEmpty()) {
            throw Exception::missingAttributes(
                $diff->implode('", "')
            );
        }

        return $this;
    }

    private function checkOptionalAttributes()
    {
        $attributes = collect(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = $this->template->keys()->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Exception::unknownAttributes(
                $diff->implode('", "')
            );
        }

        return $this;
    }

    private function checkFormat()
    {
        if ($this->template->has('lengthMenu')
            && ! $this->template->get('lengthMenu') instanceof Obj) {
            throw Exception::invalidLengthMenu();
        }

        if ($this->template->has('appends')
            && ! $this->template->get('appends') instanceof Obj) {
            throw Exception::invalidAppends();
        }

        if ($this->template->has('debounce')
            && ! is_int($this->template->get('debounce'))) {
            throw Exception::invalidDebounce();
        }

        if ($this->template->has('method')
            && ! collect(['GET', 'POST'])->contains($this->template->get('method'))) {
            throw Exception::invalidMethod();
        }

        if ($this->template->has('selectable')
            && ! is_bool($this->template->get('selectable'))) {
            throw Exception::invalidSelectable();
        }

        if ($this->template->has('comparisonOperator')
            && ! collect(['LIKE', 'ILIKE'])->contains($this->template->get('comparisonOperator'))) {
            throw Exception::invalidComparisonOperator();
        }

        if ($this->template->has('searchMode')
            && ! collect(['full', 'startsWith', 'endsWith'])->contains($this->template->get('searchMode'))) {
            throw Exception::invalidSearchMode();
        }

        if ($this->template->has('searchModes')
            && ! $this->template->get('searchModes') instanceof Obj) {
            throw Exception::invalidSearchModes();
        }
    }
}
