<?php

namespace LaravelEnso\VueDatatable\app\Classes;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\VueDatatable\app\Classes\Template\Builder;
use LaravelEnso\VueDatatable\app\Classes\Template\Validator;

class Template
{
    private $template;
    private $meta;

    public function __construct(string $filename)
    {
        $this->template = $this->template($filename);
        $this->meta = new Obj();
    }

    public function get()
    {
        (new Builder($this->template, $this->meta))
            ->run();

        return [
            'template' => $this->template,
            'meta' => $this->meta,
        ];
    }

    private function template($filename)
    {
        $template = new Obj(
            (new JsonParser($filename))
                ->array()
        );

        if ($this->needsValidation()) {
            (new Validator($template))
            ->run();
        }

        return $template;
    }

    private function needsValidation()
    {
        return ! app()->environment('production')
            || config('enso.datatable.validations') === 'always';
    }
}
