<?php

namespace LaravelEnso\VueDatatable\app\Classes;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use LaravelEnso\VueDatatable\app\Classes\Template\Builder;
use LaravelEnso\VueDatatable\app\Classes\Template\Validator;
use LaravelEnso\VueDatatable\app\Exceptions\TemplateException;

class Template
{
    private $template;

    public function __construct(string $template)
    {
        $this->set($template);
    }

    public function get()
    {
        if ($this->needsValidation()) {
            (new Validator($this->template))
                ->run();
        }

        (new Builder($this->template))
            ->run();

        return ['template' => $this->template];
    }

    private function set(string $template)
    {
        try {
            $this->template = json_decode(\File::get($template));
        } catch (FileNotFoundException $exception) {
            throw new TemplateException(__(
                'Specified template file was not found'
            ));
        }

        if (!$this->template) {
            throw new TemplateException(__(
                'Template is not readable'
            ));
        }
    }

    private function needsValidation()
    {
        return config('app.env') === 'local'
            || config('enso.datatable.validations') === 'always';
    }
}
