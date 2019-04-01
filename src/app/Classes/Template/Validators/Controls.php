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

        $this->setDefaults();
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
            ->filter(function ($control) {
                return is_string($control);
            })->diff(collect($this->defaults));

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Unknown control(s) Found: ":control"',
                ['control' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function setDefaults()
    {
        $this->defaults = collect(config('enso.datatable.controls'))->toArray();
    }
}
