<?php

namespace LaravelEnso\Tables\app\Services;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\Tables\app\Services\Template\Builder;
use LaravelEnso\Tables\app\Services\Template\Validator;

class Template
{
    private $template;
    private $meta;
    private $ready;

    public function __construct(Table $table)
    {
        $this->template = $this->template($table->templatePath());
        $this->meta = new Obj();
        $this->ready = false;
    }

    public function get()
    {
        if (! $this->ready) {
            (new Builder($this->template, $this->meta))->handle();

            $this->ready = true;
        }

        return [
            'template' => $this->template,
            'meta' => $this->meta,
            'apiVersion' => config('enso.tables.apiVersion'),
        ];
    }

    public function shouldCache()
    {
        return $this->template->has('templateCache')
            ? $this->template->get('templateCache')
            : config('enso.tables.cache.template');
    }

    private function template($filename)
    {
        $template = new Obj(
            (new JsonParser($filename))->array()
        );

        if ($this->needsValidation()) {
            (new Validator($template))->run();
        }

        return $template;
    }

    private function needsValidation()
    {
        return ! app()->environment('production')
            || config('enso.tables.validations') === 'always';
    }
}
