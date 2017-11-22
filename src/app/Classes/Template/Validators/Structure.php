<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Validators;

use LaravelEnso\VueDatatable\app\Classes\Attributes\Structure as Attributes;
use LaravelEnso\VueDatatable\app\Exceptions\TemplateException;

class Structure
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $this->checkMandatoryAttributes();
        $this->checkOptionalAttributes();
    }

    private function checkMandatoryAttributes()
    {
        $diff = collect(Attributes::Mandatory)
            ->diff(collect($this->template)->keys());

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(sprintf(
                'Mandatory Attribute(s) Missing: "%s"',
                $diff->implode('", "')
            )));
        }
    }

    private function checkOptionalAttributes()
    {
        $attributes = collect(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = collect($this->template)
            ->keys()
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(sprintf(
                'Unknown Attribute(s) Found: "%s"',
                $diff->implode('", "')
            )));
        }
    }
}
