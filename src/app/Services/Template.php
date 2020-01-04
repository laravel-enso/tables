<?php

namespace LaravelEnso\Tables\App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\App\Classes\JsonParser;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Contracts\Table;
use LaravelEnso\Tables\App\Services\Template\Builder;
use LaravelEnso\Tables\App\Services\Template\Validator;
use ReflectionClass;

class Template
{
    private Builder $builder;
    private Table $table;
    private Obj $template;
    private Obj $meta;

    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->meta = new Obj();
    }

    public function __call($method, $args)
    {
        return $this->template->{$method}(...$args);
    }

    public function load(Obj $template, Obj $meta)
    {
        $this->template = $template;
        $this->meta = $meta;

        return $this;
    }

    public function buildCacheable()
    {
        $this->template = $this->template();

        $this->builder()->handleCacheable();

        return $this;
    }

    public function buildNonCacheable()
    {
        $this->builder()->handleNonCacheable();

        return $this;
    }

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

    public function meta(): Obj
    {
        return $this->meta;
    }

    public function column(string $index)
    {
        return $this->columns()[$index];
    }

    private function builder()
    {
        return $this->builder
            ??= new Builder($this->template, $this->meta);
    }

    private function template()
    {
        $template = $this->readJson($this->table->templatePath());

        if (! $template->has('model')) {
            $model = (new ReflectionClass($this->table->query()->getModel()))
                ->getShortName();

            $template->set('model', Str::camel($model));
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
        return (new Collection([App::environment(), 'always']))->contains(
            config('enso.tables.validations')
        );
    }
}
