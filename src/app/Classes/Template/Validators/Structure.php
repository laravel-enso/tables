<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Validators;

use LaravelEnso\VueDatatable\app\Exceptions\TemplateException;
use LaravelEnso\VueDatatable\app\Classes\Attributes\Structure as Attributes;

class Structure
{
    private $template;

    public function __construct($template)
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
            ->diff(collect($this->template)->keys());

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

        $diff = collect($this->template)
            ->keys()
            ->diff($attributes);

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
        if (property_exists($this->template, 'lengthMenu') && !is_array($this->template->lengthMenu)) {
            throw new TemplateException(__('"lengthMenu" attribute must be an array'));
        }

        if (property_exists($this->template, 'appends') && !is_array($this->template->appends)) {
            throw new TemplateException(__('"appends" attribute must be an array'));
        }

        if (property_exists($this->template, 'debounce') && !is_int($this->template->debounce)) {
            throw new TemplateException(__('"debounce" attribute must be an integer'));
        }

        if (property_exists($this->template, 'method')
            && !collect(['GET', 'POST'])->contains($this->template->method)) {
            throw new TemplateException(__('"method" attribute can be either "GET" or "POST"'));
        }
    }
}
