<?php

namespace LaravelEnso\Tables\app\Services\Template\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\TemplateException;
use LaravelEnso\Tables\app\Attributes\Structure as Attributes;

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
            throw new TemplateException(__(
                'Mandatory Attribute(s) Missing: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkOptionalAttributes()
    {
        $attributes = collect(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = $this->template->keys()->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Unknown Attribute(s) Found: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkFormat()
    {
        if ($this->template->has('lengthMenu')
            && ! $this->template->get('lengthMenu') instanceof Obj) {
            throw new TemplateException(__('"lengthMenu" attribute must be an array'));
        }

        if ($this->template->has('appends')
            && ! $this->template->get('appends') instanceof Obj) {
            throw new TemplateException(__('"appends" attribute must be an array'));
        }

        if ($this->template->has('debounce')
            && ! is_int($this->template->get('debounce'))) {
            throw new TemplateException(__('"debounce" attribute must be an integer'));
        }

        if ($this->template->has('method')
            && ! collect(['GET', 'POST'])->contains($this->template->get('method'))) {
            throw new TemplateException(__('"method" attribute can be either "GET" or "POST"'));
        }

        if ($this->template->has('selectable')
            && ! is_bool($this->template->get('selectable'))) {
            throw new TemplateException(__('"selectable" attribute must be a boolean'));
        }

        if ($this->template->has('comparisonOperator')
            && ! collect(['LIKE', 'ILIKE'])->contains($this->template->get('comparisonOperator'))) {
            throw new TemplateException(__('"comparisonOperator" attribute can be either "LIKE" or "ILIKE"'));
        }
    }
}
