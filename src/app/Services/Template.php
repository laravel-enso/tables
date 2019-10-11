<?php

namespace LaravelEnso\Tables\app\Services;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\Tables\app\Services\Template\Builder;
use LaravelEnso\Tables\app\Services\Template\Validator;

class Template
{
    private $template;
    private $meta;

    public function toArray()
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

    public function column(string $index)
    {
        return $this->columns()[$index];
    }

    public function build(Table $table)
    {
        $this->template = $this->template($table);
        $this->meta = new Obj();

        (new Builder($this->template, $this->meta))->handle();

        return $this;
    }

    public function load(Obj $template, Obj $meta)
    {
        $this->template = $template;
        $this->meta = $meta;

        return $this;
    }

    public function __call($method, $args)
    {
        return $this->template->{$method}(...$args);
    }

    private function template($table)
    {
        $template = $this->readJson($table->templatePath());

        if (! $template->has('model')) {
            $model = (new ReflectionClass($table->query()->getModel()))
                ->getShortName();

            $template->set('model', Str::lower($model));
        }

        return $template;
    }

    private function readJson($path)
    {
        $template = new Obj(
            (new JsonParser($path))->array()
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
