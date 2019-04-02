<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\VueDatatable\app\Exceptions\TemplateException;

class Controls
{
    private $controls;
    private $defaults;

    public function __construct(Obj $template)
    {
        $this->controls = $template->get('controls');
        $this->defaults = config('enso.datatable.controls');
    }

    public function validate()
    {
        $this->checkFormat()
            ->checkDefault();
    }

    private function checkFormat()
    {
        $formattedWrong = collect($this->controls)
            ->filter(function ($control) {
                return ! is_string($control);
            });

        if ($formattedWrong->isNotEmpty()) {
            throw new TemplateException(__('The controls array may contain only strings.'));
        }

        return $this;
    }

    private function checkDefault()
    {
        $diff = collect($this->controls)
            ->diff($this->defaults);

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Unknown control(s) Found: ":controls"',
                ['controls' => $diff->implode('", "')]
            ));
        }

        return $this;
    }
}
