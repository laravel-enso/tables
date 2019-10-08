<?php

namespace LaravelEnso\Tables\app\Services;

use Illuminate\Support\Facades\App;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\Tables\app\Services\Template\Builder;
use LaravelEnso\Tables\app\Services\Template\Validator;

class Template
{
    private $table;
    private $template;
    private $meta;
    private $ready;

    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->meta = new Obj();
        $this->ready = false;
    }

    public function data()
    {
        return [
            'template' => $this->template,
            'meta' => $this->meta,
            'apiVersion' => config('enso.tables.apiVersion'),
        ];
    }

    public function columns()
    {
        return $this->template->get('columns');
    }

    public function meta()
    {
        return $this->meta;
    }

    public function build()
    {
        if (! $this->ready) {
            $this->template = $this->parse($this->table->templatePath());
            (new Builder($this->template, $this->meta))->handle();

            $this->ready = true;
        }
    }

    public function load($cache)
    {
        ['meta' => $this->meta, 'template' => $this->template] = $cache;

        $this->ready = true;

        return $this;
    }

    public function __call($method, $args)
    {
        return $this->template->{$method}(...$args);
    }

    private function parse($filename)
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
        return collect([App::environment(), 'always'])->contains(
            config('enso.tables.validations')
        );
    }
}
