<?php

namespace LaravelEnso\VueDatatable\app\Classes;

use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\VueDatatable\app\Classes\Template\Builder;
use LaravelEnso\VueDatatable\app\Classes\Template\Validator;

class Template
{
    private $filename;
    private $template;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function get()
    {
        $this->readTemplate();

        (new Builder($this->template))
            ->run();

        return ['template' => $this->template];
    }

    private function readTemplate()
    {
        $this->template = (new JsonParser($this->filename))->object();

        if ($this->needsValidation()) {
            (new Validator($this->template))
                ->run();
        }
    }

    private function needsValidation()
    {
        return ! app()->environment('production')
            || config('enso.datatable.validations') === 'always';
    }
}
