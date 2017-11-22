<?php

namespace LaravelEnso\VueDatatable\app\Classes;

use LaravelEnso\VueDatatable\app\Classes\Template\Builder;
use LaravelEnso\VueDatatable\app\Classes\Template\Validator;
use LaravelEnso\VueDatatable\app\Exceptions\TemplateException;

class Template
{
    private $template;
    private $validator;
    private $builder;

    public function __construct(string $template)
    {
        $this->set($template);

        $this->validator = new Validator($this->template);
        $this->builder = new Builder($this->template);
    }

    public function get()
    {
        if ($this->needsValidation()) {
            $this->validator->run();
        }

        $this->builder->run();

        return ['template' => $this->template];
    }

    private function set(string $template)
    {
        $this->template = json_decode(\File::get($template));

        if (!$this->template) {
            throw new TemplateException(__('Template is not readable'));
        }
    }

    private function needsValidation()
    {
        return config('app.env') === 'local' || config('enso.datatables.validations') === 'always';
    }
}
