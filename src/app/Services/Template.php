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
    private $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->template = $this->template($table->templatePath());
        $this->meta = new Obj();
    }

    public function get()
    {
        (new Builder($this->template, $this->meta))
            ->run();

        return [
            'template' => $this->template,
            'meta' => $this->meta,
            'apiVersion' => config('enso.tables.apiVersion'),
        ];
    }

    public function filename()
    {
        return $this->table->templatePath();
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
            || config('enso.tables.validations') === 'always';
    }
}
